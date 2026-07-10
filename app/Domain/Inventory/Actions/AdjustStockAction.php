<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Actions;

use App\Domain\Inventory\Models\StockAdjustment;
use App\Domain\Shared\Enums\MovementType;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * اعتماد تسوية جرد.
 * لا تُحرَّك أرصدة إلا بعد الاعتماد. التسوية المعتمدة لا تُعاد.
 */
final class AdjustStockAction
{
    public function __construct(private RecordStockMovementAction $recordMovement) {}

    public function approve(StockAdjustment $adjustment): StockAdjustment
    {
        if ($adjustment->isApproved()) {
            throw new RuntimeException('التسوية معتمدة بالفعل ولا يمكن اعتمادها مرتين.');
        }

        return DB::transaction(function () use ($adjustment) {
            foreach ($adjustment->lines()->orderBy('variant_id')->get() as $line) {
                $delta = (float) $line->qty_delta;

                if ($delta == 0.0) {
                    continue;   // لا فرق ⟵ لا حركة
                }

                $this->recordMovement->execute(
                    variantId:      $line->variant_id,
                    warehouseId:    $adjustment->warehouse_id,
                    type:           MovementType::Adjustment,
                    qtyDelta:       $delta,
                    unitCostMinor:  $line->variant->getRawOriginal('cost_minor') ?? 0,
                    reference:      $adjustment,
                    reasonCode:     $adjustment->reason->value,
                    note:           $adjustment->note,
                    allowNegative:  true,   // الجرد قد يكشف رصيدًا سالبًا
                );
            }

            $adjustment->update([
                'status'      => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            return $adjustment->fresh();
        }, attempts: 3);
    }
}
