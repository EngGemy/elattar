<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Models;

use App\Domain\Catalog\Models\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/** حجز مخزون بمهلة انتهاء — يمنع تجميد المخزون في السلال المتروكة */
class StockReservation extends Model
{
    protected $fillable = [
        'variant_id', 'warehouse_id', 'qty',
        'reference_type', 'reference_id', 'status', 'expires_at',
    ];

    protected $casts = ['qty' => 'decimal:3', 'expires_at' => 'datetime'];

    public function variant(): BelongsTo   { return $this->belongsTo(ProductVariant::class, 'variant_id'); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function reference(): MorphTo   { return $this->morphTo(); }

    public function scopeActive($q)  { return $q->where('status', 'active'); }
    public function scopeExpired($q) { return $q->active()->whereNotNull('expires_at')->where('expires_at', '<', now()); }
}
