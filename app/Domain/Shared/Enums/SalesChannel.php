<?php

declare(strict_types=1);

namespace App\Domain\Shared\Enums;

use Filament\Support\Contracts\HasLabel;

enum SalesChannel: string implements HasLabel
{
    case Online   = 'online';
    case Pos      = 'pos';
    case WhatsApp = 'whatsapp';

    public function getLabel(): string
    {
        return match ($this) {
            self::Online   => 'المتجر الإلكتروني',
            self::Pos      => 'نقطة البيع',
            self::WhatsApp => 'واتساب',
        };
    }
}
