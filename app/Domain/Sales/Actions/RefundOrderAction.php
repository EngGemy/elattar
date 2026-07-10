<?php

declare(strict_types=1);

namespace App\Domain\Sales\Actions;

use App\Domain\Inventory\Actions\RecordStockMovementAction;
use App\Domain\Sales\Models\Order;
use App\Domain\Sales\Models\OrderLine;
use App\Domain\Sales\Models\Refund;
use App\Domain\Sales\Models\RefundLine;
use App\Domain\Shared\Enums\MovementType;
use App\Domain\Shared\Enums\PaymentStatus;
use App\Domain\Shared\ValueObjects\Money;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * الاسترداد الجزئي أو الكلي.
 * restock=true ⟵ تُعاد الكمية للمخزون بحركة "مرتجع عميل".
 */
final class RefundOrderAction
{
    public function __construct(private RecordStockMovementAction $recordMovement) {}

    /** @param array<int, array{order_line_id:int, qty:float}> $lines */
    public function execute(
        Order $order,
        array $lines,
        string $reason,
        bool $restock = true,
        ?string $note = null,
    ): Refund {
        return DB::transaction(function () use ($order, $lines, $reason, $restock, $note) {
            $refund = Refund::create([
                'number'       => Refund::nextNumber(),
                'order_id'     => $order->id,
                'amount_minor' => 0,
                'reason'       => $reason,
                'restock'      => $restock,
                'note'         => $note,
                'status'       => 'approved',
                'created_by'   => auth()->id(),
                'approved_by'  => auth()->id(),
            ]);

            $totalRefund = Money::zero();

            foreach ($lines as $l) {
                $orderLine = OrderLine::whereKey($l['order_line_id'])
                    ->where('order_id', $order->id)
                    ->firstOrFail();

                $qty = (float) $l['qty'];

                if ($qty > (float) $orderLine->qty) {
                    throw new RuntimeException(
                        "كمية الاسترداد ({$qty}) تتجاوز الكمية المباعة ({$orderLine->qty}) للصنف «{$orderLine->name_snapshot}»."
                    );
                }

                // النسبة والتناسب من إجمالي السطر
                $ratio  = $qty / (float) $orderLine->qty;
                $amount = $orderLine->line_total_minor->multipliedBy($ratio);

                RefundLine::create([
                    'refund_id'     => $refund->id,
                    'order_line_id' => $orderLine->id,
                    'qty'           => $qty,
                    'amount_minor'  => $amount->minor,
                ]);

                if ($restock) {
                    $this->recordMovement->execute(
                        variantId:     $orderLine->variant_id,
                        warehouseId:   $order->warehouse_id,
                        type:          MovementType::CustomerReturn,
                        qtyDelta:      $qty,
                        unitCostMinor: (int) ($orderLine->getRawOriginal('cost_minor') ?? 0),
                        reference:     $refund,
                        reasonCode:    $reason,
                    );
                }

                $totalRefund = $totalRefund->plus($amount);
            }

            $refund->update(['amount_minor' => $totalRefund->minor]);

            $newRefunded = $order->refunded_minor->plus($totalRefund);

            $order->update([
                'refunded_minor' => $newRefunded->minor,
                'payment_status' => $newRefunded->minor >= $order->total_minor->minor
                    ? PaymentStatus::Refunded
                    : PaymentStatus::PartiallyRefunded,
            ]);

            return $refund->fresh('lines');
        }, attempts: 3);
    }
}
