<?php

declare(strict_types=1);

namespace App\Domain\Sales\States;

class Confirmed extends OrderState
{
    public static $name = "confirmed";

    public function label(): string { return "مؤكَّد"; }
    public function color(): string { return "info"; }
    public function icon(): string  { return "heroicon-o-check-circle"; }
}
