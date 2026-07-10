<?php

declare(strict_types=1);

namespace App\Domain\Sales\Models;

use App\Casts\MoneyCast;
use App\Domain\Shared\Concerns\GeneratesDocumentNumber;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Refund extends Model implements AuditableContract
{
    use GeneratesDocumentNumber, Auditable;

    protected $fillable = [
        'number', 'order_id', 'amount_minor', 'reason',
        'restock', 'note', 'status', 'created_by', 'approved_by',
    ];

    protected $casts = ['amount_minor' => MoneyCast::class, 'restock' => 'boolean'];

    public static function documentPrefix(): string { return 'RFN'; }

    public function order(): BelongsTo     { return $this->belongsTo(Order::class); }
    public function lines(): HasMany       { return $this->hasMany(RefundLine::class); }
    public function creator(): BelongsTo   { return $this->belongsTo(User::class, 'created_by'); }
    public function approver(): BelongsTo  { return $this->belongsTo(User::class, 'approved_by'); }
    public function movements(): MorphMany { return $this->morphMany(\App\Domain\Inventory\Models\StockMovement::class, 'reference'); }
}
