<?php

declare(strict_types=1);

namespace App\Domain\Shared\ValueObjects;

use InvalidArgumentException;

/**
 * كائن القيمة النقدية — كل المبالغ تُخزَّن بالقروش (minor units) كـ integer.
 * لا floating point مطلقًا. هذا يمنع أخطاء التقريب التراكمية في الفواتير.
 */
final readonly class Money implements \JsonSerializable, \Stringable
{
    private function __construct(
        public int $minor,
        public string $currency = 'EGP',
    ) {}

    /** إنشاء من القروش مباشرة — يقبل int أو سلسلة رقمية صحيحة (سلوك MySQL/PDO) */
    public static function ofMinor(int|string $minor, string $currency = 'EGP'): self
    {
        if (is_string($minor)) {
            $minor = trim($minor);

            if (! preg_match('/^-?\d+$/', $minor)) {
                throw new InvalidArgumentException(
                    "المبلغ بالقروش يجب أن يكون عددًا صحيحًا: «{$minor}»"
                );
            }

            $minor = (int) $minor;
        }

        return new self($minor, $currency);
    }

    /** إنشاء من الجنيهات (12.50 → 1250 قرش) */
    public static function ofMajor(float|string $major, string $currency = 'EGP'): self
    {
        return new self((int) round(((float) $major) * 100), $currency);
    }

    public static function zero(string $currency = 'EGP'): self
    {
        return new self(0, $currency);
    }

    public function plus(self $other): self
    {
        $this->assertSameCurrency($other);
        return new self($this->minor + $other->minor, $this->currency);
    }

    public function minus(self $other): self
    {
        $this->assertSameCurrency($other);
        return new self($this->minor - $other->minor, $this->currency);
    }

    /**
     * الضرب في كمية عشرية (مثلاً 0.250 كجم).
     * التقريب يحدث مرة واحدة فقط هنا — في نهاية الحساب، لا قبله.
     */
    public function multipliedBy(float|string $factor): self
    {
        return new self((int) round($this->minor * (float) $factor), $this->currency);
    }

    /** نسبة مئوية (14.0 = 14%) */
    public function percentage(float $percent): self
    {
        return new self((int) round($this->minor * $percent / 100), $this->currency);
    }

    public function isZero(): bool     { return $this->minor === 0; }
    public function isNegative(): bool { return $this->minor < 0; }
    public function isPositive(): bool { return $this->minor > 0; }

    public function equals(self $other): bool
    {
        return $this->minor === $other->minor && $this->currency === $other->currency;
    }

    public function greaterThan(self $other): bool
    {
        $this->assertSameCurrency($other);
        return $this->minor > $other->minor;
    }

    public function lessThan(self $other): bool
    {
        $this->assertSameCurrency($other);
        return $this->minor < $other->minor;
    }

    /** منع القيم السالبة (خصم يتجاوز الإجمالي) */
    public function clampToZero(): self
    {
        return $this->minor < 0 ? self::zero($this->currency) : $this;
    }

    /** أصغر القيمتين — لتطبيق سقف الخصم */
    public function min(self $other): self
    {
        $this->assertSameCurrency($other);
        return $this->minor <= $other->minor ? $this : $other;
    }

    public function toMajor(): float
    {
        return $this->minor / 100;
    }

    /** التنسيق: "1,250.50 ج.م" */
    public function format(bool $withSymbol = true): string
    {
        $formatted = number_format($this->minor / 100, 2, '.', ',');
        return $withSymbol ? "{$formatted} ج.م" : $formatted;
    }

    private function assertSameCurrency(self $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException(
                "لا يمكن الجمع بين عملتين مختلفتين: {$this->currency} و {$other->currency}"
            );
        }
    }

    public function jsonSerialize(): array
    {
        return ['minor' => $this->minor, 'currency' => $this->currency, 'formatted' => $this->format()];
    }

    public function __toString(): string
    {
        return $this->format();
    }
}
