<?php

declare(strict_types=1);

namespace App\Domain\Shared\ValueObjects;

use App\Domain\Shared\Enums\UnitOfMeasure;
use InvalidArgumentException;

/**
 * كائن الكمية — يدعم البيع بالوزن (جرام/كيلو) والقطعة.
 * دقة 3 خانات عشرية. يُخزَّن كنص ويُحسب بـ bcmath لتجنّب أخطاء الـ float.
 */
final readonly class Quantity implements \Stringable
{
    private const SCALE = 3;

    public function __construct(
        public string $value,
        public UnitOfMeasure $unit,
    ) {
        if (bccomp($this->value, '0', self::SCALE) < 0) {
            throw new InvalidArgumentException('الكمية لا يمكن أن تكون سالبة');
        }
    }

    public static function of(float|string $value, UnitOfMeasure $unit): self
    {
        return new self(number_format((float) $value, self::SCALE, '.', ''), $unit);
    }

    public static function zero(UnitOfMeasure $unit): self
    {
        return new self('0.000', $unit);
    }

    public function plus(self $other): self
    {
        $this->assertSameUnit($other);
        return new self(bcadd($this->value, $other->value, self::SCALE), $this->unit);
    }

    public function minus(self $other): self
    {
        $this->assertSameUnit($other);
        return new self(bcsub($this->value, $other->value, self::SCALE), $this->unit);
    }

    public function isZero(): bool
    {
        return bccomp($this->value, '0', self::SCALE) === 0;
    }

    public function greaterThan(self $other): bool
    {
        $this->assertSameUnit($other);
        return bccomp($this->value, $other->value, self::SCALE) > 0;
    }

    public function lessThan(self $other): bool
    {
        $this->assertSameUnit($other);
        return bccomp($this->value, $other->value, self::SCALE) < 0;
    }

    /**
     * التحقق من مضاعفات وحدة البيع.
     * البهارات تُباع بمضاعفات 50 جرام — كمية 175g مرفوضة.
     */
    public function isMultipleOf(string $step): bool
    {
        if (bccomp($step, '0', self::SCALE) === 0) {
            return true;
        }
        $quotient  = bcdiv($this->value, $step, 0);
        $remainder = bcsub($this->value, bcmul($quotient, $step, self::SCALE), self::SCALE);

        return bccomp($remainder, '0', self::SCALE) === 0;
    }

    /** التقريب لأعلى مضاعف */
    public function roundUpToStep(string $step): self
    {
        if (bccomp($step, '0', self::SCALE) === 0 || $this->isMultipleOf($step)) {
            return $this;
        }
        $quotient = bcadd(bcdiv($this->value, $step, 0), '1', 0);

        return new self(bcmul($quotient, $step, self::SCALE), $this->unit);
    }

    public function toFloat(): float
    {
        return (float) $this->value;
    }

    /** "250 جم" / "1.5 كجم" / "2 قطعة" */
    public function format(): string
    {
        $num = rtrim(rtrim($this->value, '0'), '.') ?: '0';
        return "{$num} {$this->unit->labelAr()}";
    }

    private function assertSameUnit(self $other): void
    {
        if ($this->unit !== $other->unit) {
            throw new InvalidArgumentException(
                "وحدتا القياس غير متطابقتين: {$this->unit->value} و {$other->unit->value}"
            );
        }
    }

    public function __toString(): string
    {
        return $this->format();
    }
}
