<?php

declare(strict_types=1);

namespace App\Domain\Shared\Enums;

use Filament\Support\Contracts\HasLabel;

/** وحدات القياس — أساس دعم البيع بالوزن */
enum UnitOfMeasure: string implements HasLabel
{
    case Piece = 'piece';
    case Gram  = 'gram';
    case Kg    = 'kg';
    case Liter = 'liter';
    case Ml    = 'ml';

    public function labelAr(): string
    {
        return match ($this) {
            self::Piece => 'قطعة',
            self::Gram  => 'جم',
            self::Kg    => 'كجم',
            self::Liter => 'لتر',
            self::Ml    => 'مل',
        };
    }

    public function getLabel(): string
    {
        return $this->labelAr();
    }

    /** هل تقبل كسورًا عشرية؟ */
    public function isFractional(): bool
    {
        return $this !== self::Piece;
    }

    /** الخطوة الافتراضية لأقل كمية بيع */
    public function defaultStep(): string
    {
        return match ($this) {
            self::Piece => '1.000',
            self::Gram  => '50.000',   // البهارات: مضاعفات ٥٠ جرام
            self::Kg    => '0.250',
            self::Liter => '0.500',
            self::Ml    => '100.000',
        };
    }
}
