<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Actions;

use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Inventory\Models\StockLevel;
use App\Domain\Inventory\Models\Warehouse;
use App\Domain\Shared\Enums\MovementType;

/**
 * ضبط رصيد متغيّر إلى كمية مطلقة في المخزن (افتراضي أو محدّد).
 * يُنشئ حركة تسوية بالفرق فقط — لا يكتب on_hand مباشرة.
 */
final class SetVariantStockAction
{
    public function __construct(private RecordStockMovementAction $recordMovement) {}

    public function execute(
        ProductVariant|int $variant,
        float $targetQty,
        ?int $warehouseId = null,
        ?string $note = null,
    ): StockLevel {
        $variantId = $variant instanceof ProductVariant ? (int) $variant->id : (int) $variant;
        $warehouseId ??= (int) Warehouse::query()->where('is_default', true)->value('id');

        if ($warehouseId <= 0) {
            throw new \RuntimeException('لا يوجد مخزن افتراضي لضبط الكمية.');
        }

        if ($targetQty < 0) {
            throw new \InvalidArgumentException('الكمية لا يمكن أن تكون سالبة.');
        }

        $level = StockLevel::firstOrCreate(
            ['variant_id' => $variantId, 'warehouse_id' => $warehouseId],
            ['on_hand' => 0, 'reserved' => 0]
        );

        $current = (float) $level->on_hand;
        $delta   = round($targetQty - $current, 3);

        if (abs($delta) < 0.0005) {
            return $level->fresh();
        }

        $variantModel = $variant instanceof ProductVariant
            ? $variant
            : ProductVariant::query()->findOrFail($variantId);

        $this->recordMovement->execute(
            variantId: $variantId,
            warehouseId: $warehouseId,
            type: MovementType::Adjustment,
            qtyDelta: $delta,
            unitCostMinor: (int) ($variantModel->getRawOriginal('cost_minor') ?? 0),
            reasonCode: 'manual_set',
            note: $note ?? 'تعديل كمية من بطاقة المنتج',
            allowNegative: true,
        );

        return $level->fresh();
    }
}
