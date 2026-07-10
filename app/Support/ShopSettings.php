<?php

declare(strict_types=1);

namespace App\Support;

use App\Settings\GeneralSettings;
use Illuminate\Support\Facades\Storage;

/** وصول موحّد لإعدادات المتجر — من لوحة التحكم مع fallback للـ config */
final class ShopSettings
{
    /** @return array<string, mixed> */
    public static function data(): array
    {
        $defaults = self::defaults();

        try {
            $s = app(GeneralSettings::class);

            return [
                'name'             => $s->shop_name ?: $defaults['name'],
                'tagline'          => $s->shop_tagline ?: $defaults['tagline'],
                'description'      => $s->shop_description ?: $defaults['description'],
                'logo_path'        => $s->logo_path,
                'logo_url'         => self::logoUrl($s->logo_path),
                'phone'            => $s->phone ?? '',
                'whatsapp'         => self::digits($s->whatsapp ?: $defaults['whatsapp']),
                'instapay'         => $s->instapay ?: $defaults['instapay'],
                'vodafone_cash'    => $s->vodafone_cash ?: $defaults['vodafone_cash'],
                'address'          => $s->address ?: $defaults['address'],
                'governorate'      => $s->governorate ?: $defaults['governorate'],
                'delivery_cities'  => $s->delivery_cities ?: $defaults['delivery_cities'],
                'hero_title'       => $s->hero_title ?: $defaults['hero_title'],
                'hero_subtitle'    => $s->hero_subtitle ?: $defaults['hero_subtitle'],
                'footer_note'      => $s->footer_note ?: $defaults['footer_note'],
            ];
        } catch (\Throwable) {
            return $defaults;
        }
    }

    public static function name(): string
    {
        return (string) self::data()['name'];
    }

    public static function whatsapp(): string
    {
        return (string) self::data()['whatsapp'];
    }

    public static function whatsappUrl(?string $message = null): string
    {
        $base = 'https://wa.me/' . self::whatsapp();

        return $message ? $base . '?text=' . rawurlencode($message) : $base;
    }

    public static function logoUrl(?string $path = null): ?string
    {
        if ($path === null) {
            try {
                $path = app(GeneralSettings::class)->logo_path;
            } catch (\Throwable) {
                $path = null;
            }
        }

        return self::urlForPath($path) ?? asset('images/brand-logo.png');
    }

    private static function urlForPath(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    /** @return array<string, mixed> */
    private static function defaults(): array
    {
        return [
            'name'            => 'عبد القادر العطّار',
            'tagline'         => 'عطارة وبقالة أصيلة',
            'description'     => 'أجود البهارات والمنتجات الغذائية — توصيل لكل الدقهلية',
            'logo_path'       => null,
            'logo_url'        => asset('images/brand-logo.png'),
            'phone'           => '',
            'whatsapp'        => self::digits(config('services.storefront.whatsapp', '201000000000')),
            'instapay'        => (string) config('services.storefront.payment.instapay', ''),
            'vodafone_cash'   => (string) config('services.storefront.payment.vodafone_cash', ''),
            'address'         => 'المنصورة — الدقهلية',
            'governorate'     => (string) config('services.storefront.delivery.governorate', 'الدقهلية'),
            'delivery_cities' => config('services.storefront.delivery.cities', ['المنصورة', 'طلخا']),
            'hero_title'      => 'بهارات أصيلة من قلب الدقهلية',
            'hero_subtitle'   => 'نختار أجود الحبوب والتوابل ونوصّل لباب بيتك في المنصورة وطلخا',
            'footer_note'     => 'جميع الحقوق محفوظة',
        ];
    }

    private static function digits(string $value): string
    {
        return preg_replace('/\D/', '', $value) ?? '';
    }
}
