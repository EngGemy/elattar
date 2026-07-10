<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Models;

use App\Casts\MoneyCast;
use App\Domain\Inventory\Models\StockLevel;
use App\Domain\Pricing\Models\Promotion;
use App\Domain\Pricing\Services\PromotionResolver;
use App\Domain\Shared\Enums\UnitOfMeasure;
use App\Domain\Shared\ValueObjects\Money;
use App\Domain\Shared\ValueObjects\Quantity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * المتغيّر — الوحدة الذرية للنظام كله.
 * كل حركة مخزون، كل سطر فاتورة، كل مسح باركود ⟵ يشير إلى variant_id.
 */
class ProductVariant extends Model implements AuditableContract
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'product_id', 'sku', 'barcode',
        'price_minor', 'compare_at_price_minor', 'cost_minor',
        'unit', 'step', 'unit_label',
        'weight_grams', 'dimensions', 'is_default', 'is_active',
    ];

    protected $casts = [
        'price_minor'            => MoneyCast::class,
        'compare_at_price_minor' => MoneyCast::class,
        'cost_minor'             => MoneyCast::class,
        'unit'                   => UnitOfMeasure::class,
        'step'                   => 'decimal:3',
        'dimensions'             => 'array',
        'is_default'             => 'boolean',
        'is_active'              => 'boolean',
    ];

    /** ⚠ إخفاء التكلفة عن الكاشير — الطبقة الأولى من طبقتَي الحماية */
    protected $hidden = ['cost_minor'];

    public function product(): BelongsTo { return $this->belongsTo(Product::class); }

    public function stockLevels(): HasMany { return $this->hasMany(StockLevel::class, 'variant_id'); }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class, 'variant_attribute_values', 'variant_id', 'attribute_value_id')
            ->withPivot('attribute_id');
    }

    // ── منطق التسعير
    public function price(): Money { return $this->price_minor; }
    public function cost(): Money  { return $this->cost_minor ?? Money::zero(); }

    public function activePromotion(): ?Promotion
    {
        return app(PromotionResolver::class)->bestFor($this);
    }

    public function effectivePrice(): Money
    {
        $promo = $this->activePromotion();

        return $promo ? $promo->applyTo($this->price()) : $this->price();
    }

    public function isOnSale(): bool
    {
        return $this->activePromotion() !== null;
    }

    /** هامش الربح للوحدة */
    public function marginMinor(): int
    {
        return $this->price()->minus($this->cost())->minor;
    }

    /** نسبة هامش الربح الإجمالي GP% */
    public function grossMarginPercent(): float
    {
        $price = $this->price()->minor;

        return $price === 0 ? 0.0 : round($this->marginMinor() / $price * 100, 2);
    }

    /**
     * حساب سعر كمية معيّنة.
     * البهارات: السعر مخزَّن لكل كيلو، والكمية بالجرام.
     * التقريب يحدث مرة واحدة فقط — في نهاية العملية.
     */
    public function priceFor(Quantity $qty): Money
    {
        $factor = match ($this->unit) {
            UnitOfMeasure::Gram => $qty->toFloat() / 1000,  // السعر/كجم × (جم ÷ ١٠٠٠)
            UnitOfMeasure::Ml   => $qty->toFloat() / 1000,
            default             => $qty->toFloat(),
        };

        return $this->effectivePrice()->multipliedBy($factor);
    }

    /** التحقق من مضاعفات وحدة البيع */
    public function isValidQuantity(Quantity $qty): bool
    {
        return $qty->isMultipleOf((string) $this->step);
    }

    /** أقل كمية طلب (بالوحدة المخزّنة: جم / قطعة / مل) */
    public function minOrderQty(): float
    {
        $step = (float) $this->step;

        return $step > 0 ? $step : 1.0;
    }

    /**
     * تقريب كمية الطلب لمضاعفات step مع احترام الحد الأدنى.
     * للبهارات: step=1 ⟵ أي جرام من 1 إلى المتاح.
     */
    public function normalizeOrderQty(float $qty): float
    {
        $step = $this->minOrderQty();
        $rounded = round(max($step, round($qty / $step) * $step), 3);

        return $rounded;
    }

    // ── المخزون
    public function levelAt(int $warehouseId): ?StockLevel
    {
        return $this->stockLevels()->where('warehouse_id', $warehouseId)->first();
    }

    public function availableAt(int $warehouseId): float
    {
        $level = $this->levelAt($warehouseId);

        return $level ? (float) $level->on_hand - (float) $level->reserved : 0.0;
    }

    public function totalOnHand(): float
    {
        return (float) $this->stockLevels()->sum('on_hand');
    }

    /** الاسم الكامل: "فلفل أسود — طحن ناعم" */
    public function getFullNameAttribute(): string
    {
        $attrs = $this->attributeValues->pluck('value')->implode(' / ');

        return $attrs ? "{$this->product->name} — {$attrs}" : $this->product->name;
    }

    /** لقطة الخصائص لتجميدها في سطر الفاتورة */
    public function attributesSnapshot(): array
    {
        return $this->attributeValues
            ->mapWithKeys(fn ($v) => [$v->attribute->name => $v->value])
            ->toArray();
    }

    public function scopeActive($q) { return $q->where('is_active', true); }
}
