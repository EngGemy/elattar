<?php

declare(strict_types=1);

namespace App\Domain\Sales\Actions;

use App\Domain\Inventory\Actions\RecordStockMovementAction;
use App\Domain\Inventory\Actions\ReleaseReservationAction;
use App\Domain\Sales\Events\OrderCancelled;
use App\Domain\Sales\Models\Order;
use App\Domain\Sales\Models\OrderStatusHistory;
use App\Domain\Sales\States\Cancelled;
use App\Domain\Shared\Enums\MovementType;
use Illuminate\Support\Facades\DB;

/**
 * إلغاء الطلب.
 * لو لم يُصرف بعدُ  ⟵ نفكّ الحجز فقط.
 * لو صُرف           ⟵ نُعيد الكمية بحركة عكسية (لا نحذف الحركة الأصلية).
 */
final class CancelOrderAction
{
    public function __construct(
        private ReleaseReservationAction $releaseReservation,
        private RecordStockMovementAction $recordMovement,
    ) {}

    public function execute(Order $order, ?string $reason = null): Order
    {
        return DB::transaction(function () use ($order, $reason) {
            $wasFulfilled = $order->status->isFulfilled();
            $from = $order->status::$name;

            if ($wasFulfilled) {
                foreach ($order->lines()->orderBy('variant_id')->get() as $line) {
                    $this->recordMovement->execute(
                        variantId:     $line->variant_id,
                        warehouseId:   $order->warehouse_id,
                        type:          MovementType::CustomerReturn,
                        qtyDelta:      (float) $line->qty,     // حركة عكسية موجبة
                        unitCostMinor: (int) ($line->getRawOriginal('cost_minor') ?? 0),
                        reference:     $order,
                        reasonCode:    'order_cancelled',
                        note:          $reason,
                    );
                }
            } else {
                $this->releaseReservation->execute($order, 'released');
            }

            $order->status->transitionTo(Cancelled::class);

            OrderStatusHistory::create([
                'order_id'    => $order->id,
                'from_status' => $from,
                'to_status'   => 'cancelled',
                'note'        => $reason ?? 'تم إلغاء الطلب',
                'user_id'     => auth()->id(),
            ]);

            OrderCancelled::dispatch($order->fresh());

            return $order->fresh();
        }, attempts: 3);
    }
}
