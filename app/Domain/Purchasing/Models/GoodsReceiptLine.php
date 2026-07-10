<?php

declare(strict_types=1);

namespace App\Domain\Purchasing\Models;

use App\Casts\MoneyCast;
use App\Domain\Catalog\Models\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoodsReceiptLine extends Model
{
    public $timestamps = false;

    protected $fillable = ['goods_receipt_id', 'purchase_order_line_id', 'variant_id', 'qty', 'unit_cost_minor'];
    protected $casts    = ['qty' => 'decimal:3', 'unit_cost_minor' => MoneyCast::class];

    public function receipt(): BelongsTo        { return $this->belongsTo(GoodsReceipt::class, 'goods_receipt_id'); }
    public function purchaseOrderLine(): BelongsTo { return $this->belongsTo(PurchaseOrderLine::class); }
    public function variant(): BelongsTo        { return $this->belongsTo(ProductVariant::class, 'variant_id'); }
}
