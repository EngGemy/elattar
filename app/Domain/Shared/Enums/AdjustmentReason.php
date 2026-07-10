<?php

declare(strict_types=1);

namespace App\Domain\Shared\Enums;

use Filament\Support\Contracts\HasLabel;

enum AdjustmentReason: string implements HasLabel
{
    case Damaged = 'damaged';
    case Expired = 'expired';
    case Count   = 'count';
    case Theft   = 'theft';
    case Other   = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::Damaged => 'تالف',
            self::Expired => 'منتهي الصلاحية',
            self::Count   => 'فرق جرد',
            self::Theft   => 'سرقة / فقد',
            self::Other   => 'أخرى',
        };
    }
}
