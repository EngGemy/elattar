<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Models;

use App\Domain\Catalog\Models\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * رصيد المخزون — قيمة مُشتقة مُخزَّنة (materialized).
 * لا يُعدَّل إلا داخل Transaction مع lockForUpdate.
 */
class StockLevel extends Model
{
    protected $fillable = [
        'variant_id', 'warehouse_id', 'on_hand', 'reserved',
        'reorder_point', 'reorder_qty', 'last_movement_at',
    ];

    protected $casts = [
        'on_hand'          => 'decimal:3',
        'reserved'         => 'decimal:3',
        'available'        => 'decimal:3',   // عمود محسوب في قاعدة البيانات
        'reorder_point'    => 'decimal:3',
        'reorder_qty'      => 'decimal:3',
        'last_movement_at' => 'datetime',
    ];

    public function variant(): BelongsTo   { return $this->belongsTo(ProductVariant::class, 'variant_id'); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }

    /** المتاح للبيع = الموجود − المحجوز */
    public function availableQty(): float
    {
        return (float) $this->on_hand - (float) $this->reserved;
    }

    public function isBelowReorderPoint(): bool
    {
        return $this->availableQty() <= (float) $this->reorder_point;
    }

    /** المخزون الراكد: لا حركة منذ ٩٠ يومًا */
    public function isDeadStock(int $days = 90): bool
    {
        return (float) $this->on_hand > 0
            && $this->last_movement_at?->lt(now()->subDays($days)) ?? false;
    }

    public function scopeLowStock($q)
    {
        return $q->whereColumn('on_hand', '<=', 'reorder_point')->where('on_hand', '>=', 0);
    }

    public function scopeOutOfStock($q)
    {
        return $q->whereRaw('on_hand - reserved <= 0');
    }
}
