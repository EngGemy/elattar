<?php

declare(strict_types=1);

namespace App\Domain\Shared\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/** أنواع حركة المخزون في الدفتر */
enum MovementType: string implements HasLabel, HasColor
{
    case Purchase    = 'purchase';
    case Sale        = 'sale';
    case CustomerReturn = 'return';
    case TransferIn  = 'transfer_in';
    case TransferOut = 'transfer_out';
    case Adjustment  = 'adjustment';

    public function getLabel(): string
    {
        return match ($this) {
            self::Purchase       => 'شراء (وارد)',
            self::Sale           => 'بيع (صادر)',
            self::CustomerReturn => 'مرتجع عميل',
            self::TransferIn     => 'تحويل وارد',
            self::TransferOut    => 'تحويل صادر',
            self::Adjustment     => 'تسوية جرد',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Purchase, self::TransferIn, self::CustomerReturn => 'success',
            self::Sale, self::TransferOut                          => 'danger',
            self::Adjustment                                       => 'warning',
        };
    }

    /** هل تزيد الرصيد؟ */
    public function isInbound(): bool
    {
        return in_array($this, [self::Purchase, self::TransferIn, self::CustomerReturn], true);
    }
}
