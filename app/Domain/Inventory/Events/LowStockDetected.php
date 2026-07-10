<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Events;

use App\Domain\Inventory\Models\StockLevel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LowStockDetected
{
    use Dispatchable, SerializesModels;

    public function __construct(public StockLevel $level) {}
}
