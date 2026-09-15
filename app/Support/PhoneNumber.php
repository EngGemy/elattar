<?php

declare(strict_types=1);

namespace App\Support;

/**
 * تطبيع أرقام الهاتف المصرية إلى E.164 (+20…) وأرقام خالية من الرموز.
 */
final class PhoneNumber
{
    /**
     * @return string|null E.164 مثل +201012345678 أو null إذا الرقم غير صالح
     */
    public static function toE164(?string $raw, string $defaultCountryCode = '20'): ?string
    {
        $digits = self::digits($raw);

        if ($digits === '') {
            return null;
        }

        // إزالة 00 الدولية إن وُجدت
        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        // 010… / 011… / 012… / 015…
        if (str_starts_with($digits, '0') && strlen($digits) >= 10) {
            $digits = $defaultCountryCode . substr($digits, 1);
        }

        // 10 أرقام تبدأ بـ 1 (بدون صفر الدولة)
        if (strlen($digits) === 10 && str_starts_with($digits, '1')) {
            $digits = $defaultCountryCode . $digits;
        }

        // يجب أن يبدأ برمز الدولة ويحتوي 11–15 رقمًا إجمالًا
        if (! preg_match('/^[1-9]\d{10,14}$/', $digits)) {
            return null;
        }

        return '+' . $digits;
    }

    /** أرقام فقط — مناسب لـ Green API chatId قبل @c.us */
    public static function digitsOnly(?string $raw, string $defaultCountryCode = '20'): ?string
    {
        $e164 = self::toE164($raw, $defaultCountryCode);

        return $e164 ? ltrim($e164, '+') : null;
    }

    public static function digits(?string $raw): string
    {
        return preg_replace('/\D+/', '', (string) $raw) ?? '';
    }
}
