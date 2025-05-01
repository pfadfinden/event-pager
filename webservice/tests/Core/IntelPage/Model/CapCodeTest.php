<?php

declare(strict_types=1);

namespace App\Tests\Core\IntelPage\Model;

use App\Core\IntelPage\Model\CapCode;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
final class CapCodeTest extends TestCase
{
    /**
     * @return array<string, list<int>>
     */
    public static function validIntProvider(): array
    {
        return [
            'lower bound' => [1],
            'upper bound' => [9999],
        ];
    }

    /**
     * @return array<string, list<int>>
     */
    public static function invalidIntProvider(): array
    {
        return [
            'negatives' => [-1],
            'lower bound' => [0],
            'upper bound' => [10000],
        ];
    }

    #[DataProvider('validIntProvider')]
    public function testCanBeCreatedByInt(int $testValue): void
    {
        $cc = CapCode::fromInt($testValue);
        self::assertTrue($testValue === $cc->getCode());
    }

    #[DataProvider('invalidIntProvider')]
    public function testCreateInvalidCapCode(int $testValue): void
    {
        self::expectException(InvalidArgumentException::class);
        CapCode::fromInt($testValue);
    }

    #[DataProvider('validIntProvider')]
    public function testCanBeCreatedFromString(int $testValue): void
    {
        $cc = CapCode::fromString((string) $testValue);
        self::assertTrue($testValue === $cc->getCode());
    }

    #[DataProvider('invalidIntProvider')]
    public function testCreateInvalidCapCodeFromString(int $testValue): void
    {
        self::expectException(InvalidArgumentException::class);
        CapCode::fromString((string) $testValue);
    }
}
