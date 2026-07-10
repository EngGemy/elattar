<?php

declare(strict_types=1);

namespace App\Domain\Sales\States;

class Processing extends OrderState
{
    public static $name = "processing";

    public function label(): string { return "قيد التجهيز"; }
    public function color(): string { return "warning"; }
    public function icon(): string  { return "heroicon-o-cog-6-tooth"; }
}
