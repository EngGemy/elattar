<?php

declare(strict_types=1);

namespace App\Domain\Shared\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PaymentStatus: string implements HasLabel, HasColor
{
    case Unpaid            = 'unpaid';
    case Partial           = 'partial';
    case Paid              = 'paid';
    case Refunded          = 'refunded';
    case PartiallyRefunded = 'partially_refunded';

    public function getLabel(): string
    {
        return match ($this) {
            self::Unpaid            => 'غير مدفوع',
            self::Partial           => 'مدفوع جزئيًا',
            self::Paid              => 'مدفوع',
            self::Refunded          => 'مسترد',
            self::PartiallyRefunded => 'مسترد جزئيًا',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Unpaid                              => 'danger',
            self::Partial, self::PartiallyRefunded    => 'warning',
            self::Paid                                => 'success',
            self::Refunded                            => 'gray',
        };
    }
}
