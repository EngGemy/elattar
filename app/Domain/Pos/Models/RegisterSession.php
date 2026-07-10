<?php

declare(strict_types=1);

namespace App\Domain\Pos\Models;

use App\Casts\MoneyCast;
use App\Domain\Sales\Models\OrderTender;
use App\Domain\Shared\ValueObjects\Money;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * شيفت الكاشير.
 *
 * variance_minor = المعدود فعليًا − المتوقع من النظام.
 * بدون هذا العمود لا يمكن كشف العجز أو السرقة. هو أهم رقم في الـ POS.
 */
class RegisterSession extends Model
{
    protected $fillable = [
        'register_id', 'user_id', 'opened_at', 'opening_float_minor',
        'closed_at', 'closing_counted_minor', 'expected_minor', 'variance_minor',
        'cash_sales_minor', 'card_sales_minor', 'other_sales_minor',
        'orders_count', 'status', 'note',
    ];

    protected $casts = [
        'opened_at'             => 'datetime',
        'closed_at'             => 'datetime',
        'opening_float_minor'   => MoneyCast::class,
        'closing_counted_minor' => MoneyCast::class,
        'expected_minor'        => MoneyCast::class,
        'variance_minor'        => MoneyCast::class,
        'cash_sales_minor'      => MoneyCast::class,
        'card_sales_minor'      => MoneyCast::class,
        'other_sales_minor'     => MoneyCast::class,
    ];

    public function register(): BelongsTo   { return $this->belongsTo(Register::class); }
    public function user(): BelongsTo       { return $this->belongsTo(User::class); }
    public function tenders(): HasMany      { return $this->hasMany(OrderTender::class, 'register_session_id'); }
    public function cashMovements(): HasMany { return $this->hasMany(CashMovement::class); }

    public function isOpen(): bool { return $this->status === 'open'; }

    /**
     * الرصيد النقدي المتوقع في الدرج:
     * الافتتاحي + مبيعات الكاش + الإيداعات − السحوبات
     */
    public function expectedCash(): Money
    {
        $cashSales = Money::ofMinor(
            (int) $this->tenders()->captured()->where('method', 'cash')->sum('amount_minor')
        );

        $cashIn  = Money::ofMinor((int) $this->cashMovements()->where('type', 'cash_in')->sum('amount_minor'));
        $cashOut = Money::ofMinor((int) $this->cashMovements()->where('type', 'cash_out')->sum('amount_minor'));

        return $this->opening_float_minor->plus($cashSales)->plus($cashIn)->minus($cashOut);
    }

    /** العجز (سالب) أو الزيادة (موجب) */
    public function variance(): Money
    {
        if (! $this->closing_counted_minor) {
            return Money::zero();
        }

        return $this->closing_counted_minor->minus($this->expectedCash());
    }

    public function hasShortage(): bool
    {
        return $this->variance()->isNegative();
    }
}
