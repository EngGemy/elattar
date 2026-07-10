<?php

declare(strict_types=1);

namespace App\Domain\Pricing\Enums;

use Filament\Support\Contracts\HasLabel;

enum PromotionScope: string implements HasLabel
{
    case All      = 'all';
    case Category = 'category';
    case Product  = 'product';
    case Variant  = 'variant';

    public function getLabel(): string
    {
        return match ($this) {
            self::All      => 'كل المنتجات',
            self::Category => 'تصنيفات محددة',
            self::Product  => 'منتجات محددة',
            self::Variant  => 'متغيّرات محددة',
        };
    }
}
