<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Actions;

use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Inventory\Models\StockLevel;
use Illuminate\Support\Facades\DB;

/**
 * متوسط التكلفة المتحرك (Moving Average Cost) — متوافق مع IFRS / GAAP.
 *
 *   new_cost = (old_qty × old_cost + received_qty × received_cost)
 *              ÷ (old_qty + received_qty)
 *
 * يُستدعى فقط عند استلام بضاعة (GRN) أو مرتجع مورد.
 */
final class RecalculateAverageCostAction
{
    public function execute(int $variantId, float $receivedQty, int $receivedUnitCostMinor): int
    {
        return DB::transaction(function () use ($variantId, $receivedQty, $receivedUnitCostMinor) {
            $variant = ProductVariant::whereKey($variantId)->lockForUpdate()->firstOrFail();

            // الرصيد الإجمالي عبر كل المخازن (المتوسط على مستوى الشركة لا المخزن)
            $currentQty = (float) StockLevel::where('variant_id', $variantId)->sum('on_hand');
            $currentCost = $variant->getRawOriginal('cost_minor') ?? 0;

            $totalQty = $currentQty + $receivedQty;

            // أول استلام أو رصيد صفري ⟵ التكلفة الجديدة هي تكلفة الاستلام
            if ($totalQty <= 0) {
                $newCost = $receivedUnitCostMinor;
            } else {
                $newCost = (int) round(
                    (($currentQty * $currentCost) + ($receivedQty * $receivedUnitCostMinor)) / $totalQty
                );
            }

            $variant->forceFill(['cost_minor' => $newCost])->save();

            return $newCost;
        }, attempts: 3);
    }
}
