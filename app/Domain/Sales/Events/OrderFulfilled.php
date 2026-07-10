<?php

declare(strict_types=1);

namespace App\Domain\Sales\Events;

use App\Domain\Sales\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderFulfilled
{
    use Dispatchable, SerializesModels;

    public function __construct(public Order $order) {}
}
