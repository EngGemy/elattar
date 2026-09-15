<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\PhoneNumber;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PhoneNumberTest extends TestCase
{
    #[DataProvider('e164Provider')]
    public function test_to_e164(string $input, ?string $expected): void
    {
        $this->assertSame($expected, PhoneNumber::toE164($input));
    }

    /** @return array<string, array{0: string, 1: ?string}> */
    public static function e164Provider(): array
    {
        return [
            'already international' => ['201012345678', '+201012345678'],
            'with plus'             => ['+20 101 234 5678', '+201012345678'],
            'local zero'            => ['01012345678', '+201012345678'],
            'ten digits'            => ['1012345678', '+201012345678'],
            'double zero'           => ['00201012345678', '+201012345678'],
            'empty'                 => ['', null],
            'garbage'               => ['abc', null],
        ];
    }

    public function test_digits_only_for_green_api_chat(): void
    {
        $this->assertSame('201012345678', PhoneNumber::digitsOnly('01012345678'));
    }
}
