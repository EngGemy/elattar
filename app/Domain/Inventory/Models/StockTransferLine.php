<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Models;

use App\Domain\Catalog\Models\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransferLine extends Model
{
    public $timestamps = false;

    protected $fillable = ['stock_transfer_id', 'variant_id', 'qty'];

    protected $casts = ['qty' => 'decimal:3'];

    public function transfer(): BelongsTo { return $this->belongsTo(StockTransfer::class, 'stock_transfer_id'); }
    public function variant(): BelongsTo  { return $this->belongsTo(ProductVariant::class, 'variant_id'); }
}
