<?php

declare(strict_types=1);

namespace App\Domain\Sales\States;

class Returned extends OrderState
{
    public static $name = "returned";

    public function label(): string { return "مرتجع"; }
    public function color(): string { return "danger"; }
    public function icon(): string  { return "heroicon-o-arrow-uturn-left"; }
}
