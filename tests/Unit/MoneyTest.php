<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domain\Shared\ValueObjects\Money;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_of_minor_accepts_integer(): void
    {
        $this->assertSame(1250, Money::ofMinor(1250)->minor);
    }

    public function test_of_minor_accepts_numeric_string_from_mysql(): void
    {
        $this->assertSame(1250, Money::ofMinor('1250')->minor);
    }

    public function test_of_minor_rejects_non_numeric_string(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Money::ofMinor('abc');
    }

    public function test_of_minor_rejects_decimal_string(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Money::ofMinor('12.5');
    }
}
