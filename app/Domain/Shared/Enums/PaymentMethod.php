<?php

declare(strict_types=1);

namespace App\Domain\Shared\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum PaymentMethod: string implements HasLabel, HasIcon
{
    case Cash     = 'cash';
    case Card     = 'card';
    case Wallet   = 'wallet';
    case Transfer = 'transfer';
    case Cod      = 'cod';

    public function getLabel(): string
    {
        return match ($this) {
            self::Cash     => 'نقدًا',
            self::Card     => 'بطاقة',
            self::Wallet   => 'محفظة إلكترونية',
            self::Transfer => 'تحويل بنكي',
            self::Cod      => 'دفع عند الاستلام',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Cash     => 'heroicon-o-banknotes',
            self::Card     => 'heroicon-o-credit-card',
            self::Wallet   => 'heroicon-o-device-phone-mobile',
            self::Transfer => 'heroicon-o-building-library',
            self::Cod      => 'heroicon-o-truck',
        };
    }

    /** هل تدخل صندوق الكاش في تسوية الشيفت؟ */
    public function affectsCashDrawer(): bool
    {
        return $this === self::Cash;
    }
}
