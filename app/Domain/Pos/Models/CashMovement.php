<?php

declare(strict_types=1);

namespace App\Domain\Pos\Models;

use App\Casts\MoneyCast;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** سحب أو إيداع نقدي من درج الكاشير أثناء الشيفت */
class CashMovement extends Model
{
    public $timestamps = false;

    protected $fillable = ['register_session_id', 'type', 'amount_minor', 'reason', 'user_id'];
    protected $casts    = ['amount_minor' => MoneyCast::class, 'created_at' => 'datetime'];

    public function session(): BelongsTo { return $this->belongsTo(RegisterSession::class, 'register_session_id'); }
    public function user(): BelongsTo    { return $this->belongsTo(User::class); }
}
