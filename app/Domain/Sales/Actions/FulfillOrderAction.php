<?php

declare(strict_types=1);

namespace App\Domain\Sales\Actions;

use App\Domain\Inventory\Actions\RecordStockMovementAction;
use App\Domain\Inventory\Actions\ReleaseReservationAction;
use App\Domain\Sales\Events\OrderFulfilled;
use App\Domain\Sales\Models\Order;
use App\Domain\Sales\Models\OrderStatusHistory;
use App\Domain\Sales\States\Shipped;
use App\Domain\Shared\Enums\MovementType;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * صرف الطلب: خصم فعلي من المخزون + تحويل الحجز إلى fulfilled.
 * هذه هي اللحظة الوحيدة التي ينخفض فيها on_hand بسبب البيع.
 */
final class FulfillOrderAction
{
    public function __construct(
        private RecordStockMovementAction $recordMovement,
        private ReleaseReservationAction $releaseReservation,
    ) {}

    public function execute(Order $order): Order
    {
        if ($order->status->isFulfilled()) {
            throw new RuntimeException("الطلب {$order->number} مصروف بالفعل.");
        }

        return DB::transaction(function () use ($order) {
            foreach ($order->lines()->orderBy('variant_id')->get() as $line) {
                $this->recordMovement->execute(
                    variantId:     $line->variant_id,
                    warehouseId:   $order->warehouse_id,
                    type:          MovementType::Sale,
                    qtyDelta:      -(float) $line->qty,
                    unitCostMinor: (int) ($line->getRawOriginal('cost_minor') ?? 0),   // التكلفة المجمّدة
                    reference:     $order,
                );
            }

            // الكمية خرجت فعليًا ⟵ لم تعد "محجوزة"
            $this->releaseReservation->execute($order, 'fulfilled');

            $from = $order->status::$name;
            $order->status->transitionTo(Shipped::class);

            OrderStatusHistory::create([
                'order_id'    => $order->id,
                'from_status' => $from,
                'to_status'   => 'shipped',
                'note'        => 'تم صرف الطلب وخصم المخزون',
                'user_id'     => auth()->id(),
            ]);

            OrderFulfilled::dispatch($order->fresh());

            return $order->fresh();
        }, attempts: 3);
    }
}
