<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Models;

use App\Domain\Shared\Concerns\GeneratesDocumentNumber;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class StockTransfer extends Model
{
    use GeneratesDocumentNumber;

    protected $fillable = [
        'number', 'from_warehouse_id', 'to_warehouse_id', 'status',
        'note', 'created_by', 'shipped_at', 'received_at',
    ];

    protected $casts = ['shipped_at' => 'datetime', 'received_at' => 'datetime'];

    public static function documentPrefix(): string { return 'TRF'; }

    public function fromWarehouse(): BelongsTo { return $this->belongsTo(Warehouse::class, 'from_warehouse_id'); }
    public function toWarehouse(): BelongsTo   { return $this->belongsTo(Warehouse::class, 'to_warehouse_id'); }
    public function lines(): HasMany           { return $this->hasMany(StockTransferLine::class); }
    public function creator(): BelongsTo       { return $this->belongsTo(User::class, 'created_by'); }
    public function movements(): MorphMany     { return $this->morphMany(StockMovement::class, 'reference'); }
}
