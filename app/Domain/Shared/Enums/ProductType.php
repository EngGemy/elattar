<?php

declare(strict_types=1);

namespace App\Domain\Shared\Enums;

use Filament\Support\Contracts\HasLabel;

enum ProductType: string implements HasLabel
{
    case Simple   = 'simple';    // قطعة واحدة بلا متغيّرات
    case Variable = 'variable';  // له متغيّرات (لون/حجم/طحن)
    case Weighted = 'weighted';  // يُباع بالوزن

    public function getLabel(): string
    {
        return match ($this) {
            self::Simple   => 'بسيط (بالقطعة)',
            self::Variable => 'متعدد المتغيّرات',
            self::Weighted => 'بالوزن',
        };
    }
}
