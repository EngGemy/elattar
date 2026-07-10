<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Models;

use App\Domain\Shared\Concerns\GeneratesDocumentNumber;
use App\Domain\Shared\Enums\AdjustmentReason;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class StockAdjustment extends Model implements AuditableContract
{
    use GeneratesDocumentNumber, Auditable;

    protected $fillable = [
        'number', 'warehouse_id', 'reason', 'note', 'status',
        'created_by', 'approved_by', 'approved_at',
    ];

    protected $casts = ['reason' => AdjustmentReason::class, 'approved_at' => 'datetime'];

    public static function documentPrefix(): string { return 'ADJ'; }

    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function lines(): HasMany       { return $this->hasMany(StockAdjustmentLine::class); }
    public function creator(): BelongsTo   { return $this->belongsTo(User::class, 'created_by'); }
    public function approver(): BelongsTo  { return $this->belongsTo(User::class, 'approved_by'); }
    public function movements(): MorphMany { return $this->morphMany(StockMovement::class, 'reference'); }

    public function isApproved(): bool { return $this->status === 'approved'; }
}
