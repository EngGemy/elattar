<?php

declare(strict_types=1);

namespace App\Domain\Sales\Actions;

use App\Domain\Sales\Models\Order;
use App\Domain\Sales\Models\OrderTender;
use App\Domain\Shared\Enums\PaymentMethod;
use App\Domain\Shared\Enums\PaymentStatus;
use App\Domain\Shared\ValueObjects\Money;
use Illuminate\Support\Facades\DB;

/** تسجيل دفعة (تدعم الدفع المتعدد: كاش + بطاقة على نفس الفاتورة) */
final class RecordPaymentAction
{
    public function execute(
        Order $order,
        PaymentMethod $method,
        Money $amount,
        ?Money $tendered = null,
        ?int $registerSessionId = null,
        ?string $reference = null,
    ): OrderTender {
        return DB::transaction(function () use ($order, $method, $amount, $tendered, $registerSessionId, $reference) {
            $change = $tendered && $tendered->greaterThan($amount)
                ? $tendered->minus($amount)
                : Money::zero();

            $tender = OrderTender::create([
                'order_id'            => $order->id,
                'register_session_id' => $registerSessionId,
                'method'              => $method,
                'amount_minor'        => $amount->minor,
                'tendered_minor'      => $tendered?->minor,
                'change_minor'        => $change->minor,
                'reference'           => $reference,
                'status'              => 'captured',
            ]);

            $paid = Money::ofMinor(
                (int) $order->tenders()->captured()->sum('amount_minor')
            );

            $order->update([
                'paid_minor'     => $paid->minor,
                'payment_status' => match (true) {
                    $paid->minor >= $order->total_minor->minor => PaymentStatus::Paid,
                    $paid->isPositive()                        => PaymentStatus::Partial,
                    default                                    => PaymentStatus::Unpaid,
                },
            ]);

            return $tender;
        }, attempts: 3);
    }
}
