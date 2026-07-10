<?php

declare(strict_types=1);

namespace App\Domain\Sales\States;

class Delivered extends OrderState
{
    public static $name = "delivered";

    public function label(): string { return "تم التسليم"; }
    public function color(): string { return "success"; }
    public function icon(): string  { return "heroicon-o-check-badge"; }
}
