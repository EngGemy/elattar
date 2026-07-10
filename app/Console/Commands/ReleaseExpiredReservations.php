<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Inventory\Actions\ReleaseReservationAction;
use Illuminate\Console\Command;

/** يعمل كل ٥ دقائق — يحرّر المخزون المحجوز في السلال المتروكة */
class ReleaseExpiredReservations extends Command
{
    protected $signature   = 'inventory:release-expired';
    protected $description = 'تحرير حجوزات المخزون المنتهية الصلاحية';

    public function handle(ReleaseReservationAction $action): int
    {
        $count = $action->releaseExpired();

        $this->info("تم تحرير {$count} حجزًا منتهي الصلاحية.");

        return self::SUCCESS;
    }
}
