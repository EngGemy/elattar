<?php

declare(strict_types=1);

namespace App\Support;

use App\Domain\Shared\ValueObjects\Money;

/** تحويل Money ↔ عرض Filament بالجنيه */
final class FilamentMoney
{
    public static function toMajor(mixed $state): ?float
    {
        if ($state === null || $state === '') {
            return null;
        }

        if ($state instanceof Money) {
            return round($state->minor / 100, 2);
        }

        return round((float) $state, 2);
    }

    public static function toMinor(mixed $state): ?int
    {
        if ($state === null || $state === '') {
            return null;
        }

        if ($state instanceof Money) {
            return $state->minor;
        }

        return (int) round((float) $state * 100);
    }
}
