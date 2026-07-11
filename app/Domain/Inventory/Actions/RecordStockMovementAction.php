<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Actions;

use App\Domain\Inventory\Events\StockLevelChanged;
use App\Domain\Inventory\Exceptions\InsufficientStockException;
use App\Domain\Inventory\Models\StockLevel;
use App\Domain\Inventory\Models\StockMovement;
use App\Domain\Shared\Enums\MovementType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * الحركة الذرية الوحيدة التي تلمس المخزون.
 *
 * كل شيء آخر (بيع، شراء، تسوية، تحويل) يمرّ من هنا.
 * القفل المتشائم (lockForUpdate) غير قابل للتفاوض — بدونه يحدث Overselling.
 */
final class RecordStockMovementAction
{
    /**
     * @param float $qtyDelta موجب = دخول، سالب = خروج
     */
    public function execute(
        int|string $variantId,
        int|string $warehouseId,
        MovementType $type,
        float $qtyDelta,
        int $unitCostMinor = 0,
        ?Model $reference = null,
        ?string $reasonCode = null,
        ?string $note = null,
        bool $allowNegative = false,
    ): StockMovement {
        if ($qtyDelta == 0.0) {
            throw new \InvalidArgumentException('لا يمكن تسجيل حركة بكمية صفر.');
        }

        $variantId = (int) $variantId;
        $warehouseId = (int) $warehouseId;

        return DB::transaction(function () use (
            $variantId, $warehouseId, $type, $qtyDelta,
            $unitCostMinor, $reference, $reasonCode, $note, $allowNegative
        ) {
            // ⚠ قفل الصف — يمنع سباق القراءة/الكتابة بين طلبين متزامنين
            $level = StockLevel::query()
                ->where('variant_id', $variantId)
                ->where('warehouse_id', $warehouseId)
                ->lockForUpdate()
                ->first();

            if (! $level) {
                $level = StockLevel::create([
                    'variant_id'   => $variantId,
                    'warehouse_id' => $warehouseId,
                    'on_hand'      => 0,
                    'reserved'     => 0,
                ]);
                $level = StockLevel::whereKey($level->id)->lockForUpdate()->first();
            }

            $newOnHand = (float) $level->on_hand + $qtyDelta;

            if (! $allowNegative && $newOnHand < 0) {
                throw new InsufficientStockException(
                    variantId:   $variantId,
                    requested:   abs($qtyDelta),
                    available:   (float) $level->on_hand,
                    productName: $level->variant?->full_name ?? "#{$variantId}",
                );
            }

            $level->update([
                'on_hand'          => $newOnHand,
                'last_movement_at' => now(),
            ]);

            $movement = StockMovement::create([
                'variant_id'      => $variantId,
                'warehouse_id'    => $warehouseId,
                'type'            => $type,
                'qty_delta'       => $qtyDelta,
                'balance_after'   => $newOnHand,       // الرصيد الجاري — عمود التدقيق
                'unit_cost_minor' => $unitCostMinor,
                'reason_code'     => $reasonCode,
                'note'            => $note,
                'reference_type'  => $reference?->getMorphClass(),
                'reference_id'    => $reference?->getKey(),
                'user_id'         => auth()->id(),
            ]);

            StockLevelChanged::dispatch($level->fresh());

            return $movement;
        }, attempts: 3);   // إعادة المحاولة عند deadlock
    }
}
