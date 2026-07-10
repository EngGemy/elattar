<?php

declare(strict_types=1);

namespace App\Domain\Purchasing\Models;

use App\Casts\MoneyCast;
use App\Domain\Inventory\Models\Warehouse;
use App\Domain\Shared\Concerns\GeneratesDocumentNumber;
use App\Domain\Shared\ValueObjects\Money;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class PurchaseOrder extends Model implements AuditableContract
{
    use GeneratesDocumentNumber, Auditable;

    protected $fillable = [
        'number', 'supplier_id', 'warehouse_id', 'status',
        'subtotal_minor', 'tax_minor', 'shipping_minor', 'total_minor', 'paid_minor',
        'expected_at', 'notes', 'created_by',
    ];

    protected $casts = [
        'subtotal_minor' => MoneyCast::class,
        'tax_minor'      => MoneyCast::class,
        'shipping_minor' => MoneyCast::class,
        'total_minor'    => MoneyCast::class,
        'paid_minor'     => MoneyCast::class,
        'expected_at'    => 'date',
    ];

    public static function documentPrefix(): string { return 'PO'; }

    public function supplier(): BelongsTo  { return $this->belongsTo(Supplier::class); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function lines(): HasMany       { return $this->hasMany(PurchaseOrderLine::class); }
    public function receipts(): HasMany    { return $this->hasMany(GoodsReceipt::class); }
    public function creator(): BelongsTo   { return $this->belongsTo(User::class, 'created_by'); }

    /** هل استُلمت كل الكميات؟ */
    public function isFullyReceived(): bool
    {
        return $this->lines->every(fn ($l) => (float) $l->qty_received >= (float) $l->qty_ordered);
    }

    public function refreshStatus(): void
    {
        $anyReceived = $this->lines->contains(fn ($l) => (float) $l->qty_received > 0);

        $this->update([
            'status' => match (true) {
                $this->isFullyReceived() => 'received',
                $anyReceived             => 'partially_received',
                default                  => $this->status,
            },
        ]);
    }

    public function totalCost(): Money
    {
        return $this->total_minor ?? Money::zero();
    }
}
