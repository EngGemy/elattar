<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Actions;

use App\Domain\Inventory\Models\StockTransfer;
use App\Domain\Shared\Enums\MovementType;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * التحويل بين المخازن — عمليتان في معاملة واحدة.
 * إما أن تنجح الحركتان (خروج + دخول) أو لا شيء. لا حالة وسطى.
 */
final class TransferStockAction
{
    public function __construct(private RecordStockMovementAction $recordMovement) {}

    public function execute(StockTransfer $transfer): StockTransfer
    {
        if ($transfer->status === 'received') {
            throw new RuntimeException('التحويل مُستلَم بالفعل.');
        }

        if ($transfer->from_warehouse_id === $transfer->to_warehouse_id) {
            throw new RuntimeException('لا يمكن التحويل من وإلى نفس المخزن.');
        }

        return DB::transaction(function () use ($transfer) {
            foreach ($transfer->lines()->orderBy('variant_id')->get() as $line) {
                $cost = $line->variant->getRawOriginal('cost_minor') ?? 0;

                // خروج من المصدر — يفشل لو الرصيد غير كافٍ
                $this->recordMovement->execute(
                    variantId:     $line->variant_id,
                    warehouseId:   $transfer->from_warehouse_id,
                    type:          MovementType::TransferOut,
                    qtyDelta:      -(float) $line->qty,
                    unitCostMinor: $cost,
                    reference:     $transfer,
                );

                // دخول للوجهة
                $this->recordMovement->execute(
                    variantId:     $line->variant_id,
                    warehouseId:   $transfer->to_warehouse_id,
                    type:          MovementType::TransferIn,
                    qtyDelta:      (float) $line->qty,
                    unitCostMinor: $cost,
                    reference:     $transfer,
                );
            }

            $transfer->update(['status' => 'received', 'received_at' => now()]);

            return $transfer->fresh();
        }, attempts: 3);
    }
}
