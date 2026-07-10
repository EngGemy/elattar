<?php

declare(strict_types=1);

namespace App\Domain\Sales\States;

class Shipped extends OrderState
{
    public static $name = "shipped";

    public function label(): string { return "تم الشحن"; }
    public function color(): string { return "primary"; }
    public function icon(): string  { return "heroicon-o-truck"; }
}
