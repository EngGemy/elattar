<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Actions;

use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Inventory\Exceptions\InsufficientStockException;
use App\Domain\Inventory\Exceptions\InvalidQuantityStepException;
use App\Domain\Inventory\Models\StockLevel;
use App\Domain\Inventory\Models\StockReservation;
use App\Domain\Shared\ValueObjects\Quantity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * حجز الكمية دون خصمها من on_hand.
 *
 * available = on_hand - reserved
 * الطلب المُنشأ يحجز، والطلب المُصرَّف يخصم ويفكّ الحجز.
 * هذا يمنع بيع نفس الكيلو مرتين لعميلين متزامنين.
 */
final class ReserveStockAction
{
    /** @param array<int, array{variant_id:int, qty:float}> $lines */
    public function execute(
        array $lines,
        int|string $warehouseId,
        ?Model $reference = null,
        ?int $ttlMinutes = null,
    ): array {
        $warehouseId = (int) $warehouseId;

        return DB::transaction(function () use ($lines, $warehouseId, $reference, $ttlMinutes) {
            $reservations = [];

            // ترتيب الأسطر حسب variant_id يمنع الـ deadlock عند التقاطع
            usort($lines, fn ($a, $b) => $a['variant_id'] <=> $b['variant_id']);

            foreach ($lines as $line) {
                $variant = ProductVariant::with('product')->findOrFail($line['variant_id']);
                $qty     = (float) $line['qty'];

                // التحقق من مضاعفات وحدة البيع (٥٠ جرام للبهارات)
                $qtyVo = Quantity::of($qty, $variant->unit);
                if (! $variant->isValidQuantity($qtyVo)) {
                    throw new InvalidQuantityStepException($qty, (string) $variant->step, $variant->unit->labelAr());
                }

                $level = StockLevel::query()
                    ->where('variant_id', $variant->id)
                    ->where('warehouse_id', $warehouseId)
                    ->lockForUpdate()
                    ->first();

                $available = $level ? (float) $level->on_hand - (float) $level->reserved : 0.0;

                if ($available < $qty) {
                    throw new InsufficientStockException(
                        variantId:   $variant->id,
                        requested:   $qty,
                        available:   $available,
                        productName: $variant->full_name,
                    );
                }

                $level->increment('reserved', $qty);

                $reservations[] = StockReservation::create([
                    'variant_id'     => $variant->id,
                    'warehouse_id'   => $warehouseId,
                    'qty'            => $qty,
                    'reference_type' => $reference?->getMorphClass(),
                    'reference_id'   => $reference?->getKey(),
                    'status'         => 'active',
                    'expires_at'     => $ttlMinutes ? now()->addMinutes($ttlMinutes) : null,
                ]);
            }

            return $reservations;
        }, attempts: 3);
    }
}
