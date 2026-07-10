<?php

declare(strict_types=1);

namespace App\Domain\Sales\Models;

use App\Casts\MoneyCast;
use App\Domain\Pos\Models\RegisterSession;
use App\Domain\Shared\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** دفعة واحدة ضمن فاتورة. جدول منفصل ⟵ الدفع المتعدد مجاني. */
class OrderTender extends Model
{
    protected $fillable = [
        'order_id', 'register_session_id', 'method',
        'amount_minor', 'tendered_minor', 'change_minor', 'reference', 'status',
    ];

    protected $casts = [
        'method'         => PaymentMethod::class,
        'amount_minor'   => MoneyCast::class,
        'tendered_minor' => MoneyCast::class,
        'change_minor'   => MoneyCast::class,
    ];

    public function order(): BelongsTo   { return $this->belongsTo(Order::class); }
    public function session(): BelongsTo { return $this->belongsTo(RegisterSession::class, 'register_session_id'); }

    public function scopeCaptured($q) { return $q->where('status', 'captured'); }
}
