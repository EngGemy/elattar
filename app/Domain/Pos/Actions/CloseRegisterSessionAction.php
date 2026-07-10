<?php

declare(strict_types=1);

namespace App\Domain\Pos\Actions;

use App\Domain\Pos\Models\RegisterSession;
use App\Domain\Shared\ValueObjects\Money;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * إقفال الشيفت وحساب الفرق (variance).
 * variance = المعدود فعليًا − المتوقع.
 * سالب = عجز. هذا الرقم هو أساس المساءلة.
 */
final class CloseRegisterSessionAction
{
    public function execute(RegisterSession $session, Money $countedCash, ?string $note = null): RegisterSession
    {
        if (! $session->isOpen()) {
            throw new RuntimeException('الشيفت مغلق بالفعل.');
        }

        return DB::transaction(function () use ($session, $countedCash, $note) {
            $expected = $session->expectedCash();
            $variance = $countedCash->minus($expected);

            $cash  = (int) $session->tenders()->captured()->where('method', 'cash')->sum('amount_minor');
            $card  = (int) $session->tenders()->captured()->where('method', 'card')->sum('amount_minor');
            $other = (int) $session->tenders()->captured()->whereNotIn('method', ['cash', 'card'])->sum('amount_minor');

            $session->update([
                'closed_at'             => now(),
                'closing_counted_minor' => $countedCash->minor,
                'expected_minor'        => $expected->minor,
                'variance_minor'        => $variance->minor,
                'cash_sales_minor'      => $cash,
                'card_sales_minor'      => $card,
                'other_sales_minor'     => $other,
                'orders_count'          => $session->tenders()->captured()->distinct('order_id')->count('order_id'),
                'status'                => 'closed',
                'note'                  => $note,
            ]);

            return $session->fresh();
        }, attempts: 3);
    }
}
