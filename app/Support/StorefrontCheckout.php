<?php

declare(strict_types=1);

namespace App\Support;

final class StorefrontCheckout
{
    public static function governorate(): string
    {
        return (string) ShopSettings::data()['governorate'];
    }

    /** @return list<string> */
    public static function cities(): array
    {
        return ShopSettings::data()['delivery_cities'];
    }

    public static function instapayNumber(): string
    {
        return (string) ShopSettings::data()['instapay'];
    }

    public static function vodafoneCashNumber(): string
    {
        return (string) ShopSettings::data()['vodafone_cash'];
    }

    public static function paymentLabel(string $method): string
    {
        return match ($method) {
            'instapay'      => 'إنستاباي',
            'vodafone_cash' => 'فودافون كاش',
            default         => 'كاش عند الاستلام',
        };
    }

    public static function paymentNumber(string $method): ?string
    {
        return match ($method) {
            'instapay'      => self::instapayNumber() ?: null,
            'vodafone_cash' => self::vodafoneCashNumber() ?: null,
            default         => null,
        };
    }
}
