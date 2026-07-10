<?php

declare(strict_types=1);

namespace App\Domain\Sales\Models;

use App\Casts\MoneyCast;
use App\Domain\Crm\Models\Customer;
use App\Domain\Inventory\Models\Warehouse;
use App\Domain\Pricing\Models\ShippingMethod;
use App\Domain\Sales\States\OrderState;
use App\Domain\Shared\Concerns\GeneratesDocumentNumber;
use App\Domain\Shared\Enums\PaymentMethod;
use App\Domain\Shared\Enums\PaymentStatus;
use App\Domain\Shared\Enums\SalesChannel;
use App\Domain\Shared\ValueObjects\Money;
use App\Support\StorefrontCheckout;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Spatie\ModelStates\HasStates;

/**
 * الطلب — وثيقة مجمّدة.
 *
 * كل الأرقام (subtotal, cogs, العناوين) لقطات وقت البيع.
 * تغيير سعر المنتج أو عنوان العميل غدًا لا يمسّ هذه الوثيقة.
 */
class Order extends Model implements AuditableContract
{
    use HasFactory, SoftDeletes, HasStates, GeneratesDocumentNumber, Auditable;

    protected $fillable = [
        'number', 'customer_id', 'warehouse_id', 'channel', 'status', 'payment_status',
        'subtotal_minor', 'discount_minor', 'tax_minor', 'shipping_minor',
        'total_minor', 'paid_minor', 'refunded_minor', 'cogs_minor', 'currency',
        'shipping_address', 'billing_address', 'shipping_method_id',
        'shipping_carrier', 'tracking_number', 'coupon_code',
        'idempotency_key', 'notes', 'internal_notes',
        'created_by', 'placed_at', 'delivered_at',
    ];

    protected $casts = [
        'status'           => OrderState::class,
        'channel'          => SalesChannel::class,
        'payment_status'   => PaymentStatus::class,
        'subtotal_minor'   => MoneyCast::class,
        'discount_minor'   => MoneyCast::class,
        'tax_minor'        => MoneyCast::class,
        'shipping_minor'   => MoneyCast::class,
        'total_minor'      => MoneyCast::class,
        'paid_minor'       => MoneyCast::class,
        'refunded_minor'   => MoneyCast::class,
        'cogs_minor'       => MoneyCast::class,
        'shipping_address' => 'array',
        'billing_address'  => 'array',
        'placed_at'        => 'datetime',
        'delivered_at'     => 'datetime',
    ];

    /** ⚠ لا يراها الكاشير */
    protected $hidden = ['cogs_minor'];

    public static function documentPrefix(): string { return 'ORD'; }

    // ── العلاقات
    public function customer(): BelongsTo       { return $this->belongsTo(Customer::class); }
    public function warehouse(): BelongsTo      { return $this->belongsTo(Warehouse::class); }
    public function shippingMethod(): BelongsTo { return $this->belongsTo(ShippingMethod::class); }
    public function creator(): BelongsTo        { return $this->belongsTo(User::class, 'created_by'); }

    public function lines(): HasMany       { return $this->hasMany(OrderLine::class); }
    public function tenders(): HasMany     { return $this->hasMany(OrderTender::class); }
    public function refunds(): HasMany     { return $this->hasMany(Refund::class); }
    public function invoice(): HasMany     { return $this->hasMany(Invoice::class); }
    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->latest('created_at');
    }

    public function movements(): MorphMany  { return $this->morphMany(\App\Domain\Inventory\Models\StockMovement::class, 'reference'); }
    public function reservations(): MorphMany { return $this->morphMany(\App\Domain\Inventory\Models\StockReservation::class, 'reference'); }

    // ── منطق مالي
    /** إجمالي الربح = صافي المبيعات − تكلفة البضاعة المباعة */
    public function grossProfit(): Money
    {
        return $this->subtotal_minor
            ->minus($this->discount_minor)
            ->minus($this->cogs_minor);
    }

    /** نسبة هامش الربح GP% */
    public function grossMarginPercent(): float
    {
        $net = $this->subtotal_minor->minus($this->discount_minor)->minor;

        return $net === 0 ? 0.0 : round($this->grossProfit()->minor / $net * 100, 2);
    }

    /** المبلغ المتبقي غير المسدَّد */
    public function balanceDue(): Money
    {
        return $this->total_minor->minus($this->paid_minor)->clampToZero();
    }

    public function isPaid(): bool
    {
        return $this->paid_minor->minor >= $this->total_minor->minor;
    }

    /** التحقق من صحة الفاتورة: مجموع الدفعات = الإجمالي */
    public function tendersMatchTotal(): bool
    {
        return (int) $this->tenders()->where('status', 'captured')->sum('amount_minor')
            === $this->total_minor->minor;
    }

    /** تسمية طريقة الدفع — POS من الدفعات، المتجر من العنوان */
    public function paymentMethodLabel(): string
    {
        if ($this->channel === SalesChannel::Pos) {
            $labels = $this->tenders()
                ->captured()
                ->get()
                ->map(fn (OrderTender $t) => $t->method->getLabel())
                ->unique()
                ->values();

            return $labels->isNotEmpty() ? $labels->implode(' + ') : PaymentMethod::Cash->getLabel();
        }

        return StorefrontCheckout::paymentLabel((string) ($this->shipping_address['payment_method'] ?? 'cod'));
    }

    public function totalTendered(): Money
    {
        $tendered = (int) $this->tenders()->captured()->sum('tendered_minor');

        return Money::ofMinor($tendered > 0 ? $tendered : (int) $this->getRawOriginal('paid_minor'));
    }

    public function totalChange(): Money
    {
        return Money::ofMinor((int) $this->tenders()->captured()->sum('change_minor'));
    }

    // ── Scopes
    public function scopePlacedBetween($q, $from, $to) { return $q->whereBetween('placed_at', [$from, $to]); }
    public function scopeChannel($q, SalesChannel $c)  { return $q->where('channel', $c); }
    public function scopeRevenueGenerating($q)
    {
        return $q->whereNotIn('status', ['cancelled', 'returned']);
    }
}
