<?php

declare(strict_types=1);

namespace App\Domain\Shared\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ProductStatus: string implements HasLabel, HasColor
{
    case Draft    = 'draft';
    case Active   = 'active';
    case Archived = 'archived';

    public function getLabel(): string
    {
        return match ($this) {
            self::Draft    => 'مسودة',
            self::Active   => 'نشط',
            self::Archived => 'مؤرشف',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Draft    => 'warning',
            self::Active   => 'success',
            self::Archived => 'gray',
        };
    }
}
