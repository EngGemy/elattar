<?php

declare(strict_types=1);

namespace App\Domain\Purchasing\Models;

use App\Domain\Inventory\Models\Warehouse;
use App\Domain\Shared\Concerns\GeneratesDocumentNumber;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/** إذن استلام بضاعة — هو المستند الذي يحرّك المخزون ويعيد حساب متوسط التكلفة */
class GoodsReceipt extends Model
{
    use GeneratesDocumentNumber;

    protected $fillable = [
        'number', 'purchase_order_id', 'warehouse_id',
        'supplier_invoice_no', 'note', 'received_by', 'received_at',
    ];

    protected $casts = ['received_at' => 'datetime'];

    public static function documentPrefix(): string { return 'GRN'; }

    public function purchaseOrder(): BelongsTo { return $this->belongsTo(PurchaseOrder::class); }
    public function warehouse(): BelongsTo     { return $this->belongsTo(Warehouse::class); }
    public function lines(): HasMany           { return $this->hasMany(GoodsReceiptLine::class); }
    public function receiver(): BelongsTo      { return $this->belongsTo(User::class, 'received_by'); }
    public function movements(): MorphMany     { return $this->morphMany(\App\Domain\Inventory\Models\StockMovement::class, 'reference'); }
}
