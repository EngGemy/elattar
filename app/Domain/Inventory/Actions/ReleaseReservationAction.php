<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Actions;

use App\Domain\Inventory\Models\StockLevel;
use App\Domain\Inventory\Models\StockReservation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/** فكّ الحجز: عند الإلغاء (released) أو الصرف (fulfilled) */
final class ReleaseReservationAction
{
    public function execute(Model $reference, string $newStatus = 'released'): int
    {
        return DB::transaction(function () use ($reference, $newStatus) {
            $reservations = StockReservation::query()
                ->where('reference_type', $reference->getMorphClass())
                ->where('reference_id', $reference->getKey())
                ->where('status', 'active')
                ->orderBy('variant_id')     // منع الـ deadlock
                ->lockForUpdate()
                ->get();

            foreach ($reservations as $res) {
                StockLevel::query()
                    ->where('variant_id', $res->variant_id)
                    ->where('warehouse_id', $res->warehouse_id)
                    ->lockForUpdate()
                    ->decrement('reserved', (float) $res->qty);

                $res->update(['status' => $newStatus]);
            }

            return $reservations->count();
        }, attempts: 3);
    }

    /** Job دوري: تحرير الحجوزات المنتهية (السلال المتروكة) */
    public function releaseExpired(): int
    {
        $count = 0;

        StockReservation::expired()->chunkById(200, function ($chunk) use (&$count) {
            foreach ($chunk as $res) {
                DB::transaction(function () use ($res, &$count) {
                    StockLevel::query()
                        ->where('variant_id', $res->variant_id)
                        ->where('warehouse_id', $res->warehouse_id)
                        ->lockForUpdate()
                        ->decrement('reserved', (float) $res->qty);

                    $res->update(['status' => 'released']);
                    $count++;
                });
            }
        });

        return $count;
    }
}
