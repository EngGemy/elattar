<?php

declare(strict_types=1);

namespace App\Domain\Sales\States;

class Cancelled extends OrderState
{
    public static $name = "cancelled";

    public function label(): string { return "ملغي"; }
    public function color(): string { return "danger"; }
    public function icon(): string  { return "heroicon-o-x-circle"; }
}
