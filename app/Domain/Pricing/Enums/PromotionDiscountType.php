<?php

declare(strict_types=1);

namespace App\Domain\Pricing\Enums;

use Filament\Support\Contracts\HasLabel;

enum PromotionDiscountType: string implements HasLabel
{
    case Percent     = 'percent';
    case FixedAmount = 'fixed_amount';
    case FixedPrice  = 'fixed_price';

    public function getLabel(): string
    {
        return match ($this) {
            self::Percent     => 'نسبة مئوية',
            self::FixedAmount => 'مبلغ ثابت',
            self::FixedPrice  => 'سعر ثابت',
        };
    }
}
