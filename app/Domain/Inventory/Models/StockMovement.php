<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Models;

use App\Casts\MoneyCast;
use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Shared\Enums\MovementType;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use RuntimeException;

/**
 * دفتر حركات المخزون — Append-only Ledger.
 *
 * ⚠ ممنوع منعًا باتًا: التعديل أو الحذف.
 * تصحيح الخطأ = إنشاء حركة عكسية. هذا شرط قابلية التدقيق (Auditability).
 */
class StockMovement extends Model
{
    public const UPDATED_AT = null;   // لا عمود updated_at — السجل ثابت

    protected $fillable = [
        'variant_id', 'warehouse_id', 'type', 'qty_delta', 'balance_after',
        'unit_cost_minor', 'reason_code', 'note',
        'reference_type', 'reference_id', 'user_id',
    ];

    protected $casts = [
        'type'            => MovementType::class,
        'qty_delta'       => 'decimal:3',
        'balance_after'   => 'decimal:3',
        'unit_cost_minor' => MoneyCast::class,
        'created_at'      => 'datetime',
    ];

    protected static function booted(): void
    {
        // حراسة على مستوى الكود — الطبقة الأولى
        static::updating(fn () => throw new RuntimeException(
            'دفتر حركات المخزون غير قابل للتعديل. أنشئ حركة عكسية بدلًا من ذلك.'
        ));

        static::deleting(fn () => throw new RuntimeException(
            'دفتر حركات المخزون غير قابل للحذف. أنشئ حركة عكسية بدلًا من ذلك.'
        ));

        // منع الحركات الصفرية (ضوضاء في الدفتر)
        static::creating(function (self $m) {
            if ((float) $m->qty_delta == 0.0) {
                throw new RuntimeException('لا يمكن تسجيل حركة مخزون بكمية صفر.');
            }
        });
    }

    public function variant(): BelongsTo   { return $this->belongsTo(ProductVariant::class, 'variant_id'); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function user(): BelongsTo      { return $this->belongsTo(User::class); }
    public function reference(): MorphTo   { return $this->morphTo(); }

    public function isInbound(): bool { return (float) $this->qty_delta > 0; }

    /** قيمة الحركة = الكمية × تكلفة الوحدة */
    public function valueMinor(): int
    {
        return (int) round(abs((float) $this->qty_delta) * $this->unit_cost_minor->minor);
    }
}
