<?php

declare(strict_types=1);

namespace App\Domain\Sales\States;

class Pending extends OrderState
{
    public static $name = "pending";

    public function label(): string { return "قيد الانتظار"; }
    public function color(): string { return "gray"; }
    public function icon(): string  { return "heroicon-o-clock"; }
}
