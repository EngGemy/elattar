<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Exceptions;

use RuntimeException;

/** الكمية ليست من مضاعفات وحدة البيع (مثلاً 175 جم والخطوة 50 جم) */
class InvalidQuantityStepException extends RuntimeException
{
    public function __construct(float $qty, string $step, string $unitLabel)
    {
        parent::__construct(
            "الكمية {$qty} {$unitLabel} غير صالحة. يجب أن تكون من مضاعفات {$step} {$unitLabel}."
        );
    }
}
