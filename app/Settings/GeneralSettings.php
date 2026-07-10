<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $shop_name;

    public string $shop_tagline;

    public string $shop_description;

    public ?string $logo_path;

    public ?string $phone;

    public string $whatsapp;

    public ?string $instapay;

    public ?string $vodafone_cash;

    public string $address;

    public string $governorate;

    /** @var list<string> */
    public array $delivery_cities;

    public string $hero_title;

    public string $hero_subtitle;

    public string $footer_note;

    public static function group(): string
    {
        return 'general';
    }
}
