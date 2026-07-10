<?php

declare(strict_types=1);

namespace App\Casts;

use App\Domain\Shared\ValueObjects\Money;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * تحويل عمود bigint (قروش) ↔ كائن Money.
 * يُطبّق Money::ofMinor() على القيمة الخام — يتعامل مع سلاسل MySQL/PDO تلقائيًا.
 *
 * الاستخدام: protected $casts = ['price_minor' => MoneyCast::class];
 */
class MoneyCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Money
    {
        if ($value === null) {
            return null;
        }

        $currency = $attributes['currency'] ?? 'EGP';

        return Money::ofMinor($value, $currency);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        if ($value === null) {
            return [$key => null];
        }

        if ($value instanceof Money) {
            return [$key => $value->minor];
        }

        return [$key => Money::ofMinor($value)->minor];
    }
}
