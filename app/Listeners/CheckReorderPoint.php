<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Domain\Inventory\Events\LowStockDetected;
use App\Domain\Inventory\Events\StockLevelChanged;
use Illuminate\Contracts\Queue\ShouldQueue;

/** بعد كل حركة مخزون: هل نزل الرصيد تحت حد إعادة الطلب؟ */
class CheckReorderPoint implements ShouldQueue
{
    public function handle(StockLevelChanged $event): void
    {
        if ($event->level->isBelowReorderPoint()) {
            LowStockDetected::dispatch($event->level);
        }
    }
}
