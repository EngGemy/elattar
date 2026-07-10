<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Exceptions;

use RuntimeException;

class InsufficientStockException extends RuntimeException
{
    public function __construct(
        public readonly int $variantId,
        public readonly float $requested,
        public readonly float $available,
        public readonly string $productName = '',
    ) {
        parent::__construct(
            "الكمية غير كافية للمنتج «{$productName}»: المطلوب {$requested}، المتاح {$available}"
        );
    }
}
