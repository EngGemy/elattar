<?php

declare(strict_types=1);

namespace App\Domain\Purchasing\Models;

use App\Casts\MoneyCast;
use App\Domain\Catalog\Models\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderLine extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'purchase_order_id', 'variant_id',
        'qty_ordered', 'qty_received', 'unit_cost_minor', 'line_total_minor',
    ];

    protected $casts = [
        'qty_ordered'      => 'decimal:3',
        'qty_received'     => 'decimal:3',
        'unit_cost_minor'  => MoneyCast::class,
        'line_total_minor' => MoneyCast::class,
    ];

    public function purchaseOrder(): BelongsTo { return $this->belongsTo(PurchaseOrder::class); }
    public function variant(): BelongsTo       { return $this->belongsTo(ProductVariant::class, 'variant_id'); }

    public function qtyPending(): float
    {
        return max(0, (float) $this->qty_ordered - (float) $this->qty_received);
    }
}
