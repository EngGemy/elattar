<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Models;

use App\Domain\Catalog\Models\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustmentLine extends Model
{
    public $timestamps = false;

    protected $fillable = ['stock_adjustment_id', 'variant_id', 'qty_before', 'qty_counted', 'qty_delta'];

    protected $casts = [
        'qty_before'  => 'decimal:3',
        'qty_counted' => 'decimal:3',
        'qty_delta'   => 'decimal:3',
    ];

    public function adjustment(): BelongsTo { return $this->belongsTo(StockAdjustment::class, 'stock_adjustment_id'); }
    public function variant(): BelongsTo    { return $this->belongsTo(ProductVariant::class, 'variant_id'); }
}
