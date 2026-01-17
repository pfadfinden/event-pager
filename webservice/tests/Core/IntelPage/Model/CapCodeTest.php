<?php

declare(strict_types=1);

namespace App\Tests\Core\IntelPage\Model;

use App\Core\IntelPage\Model\CapCode;
use InvalidArgumentException;
use Iterator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(CapCode::class)]
#[Group('unit')]
final class CapCodeTest extends TestCase
{
    /**
     * @return Iterator<string, list<int>>
     */
    public static function validIntProvider(): Iterator
    {
        yield 'lower bound' => [1];
        yield 'upper bound' => [9999];
    }

    /**
     * @return Iterator<string, list<int>>
     */
    public static function invalidIntProvider(): Iterator
    {
        yield 'negatives' => [-1];
        yield 'lower bound' => [0];
        yield 'upper bound' => [10000];
    }

    #[DataProvider('validIntProvider')]
    public function testCanBeCreatedByInt(int $testValue): void
    {
        $cc = CapCode::fromInt($testValue);
        self::assertSame($cc->getCode(), $testValue);
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
        self::assertSame($cc->getCode(), $testValue);
    }

    #[DataProvider('invalidIntProvider')]
    public function testCreateInvalidCapCodeFromString(int $testValue): void
    {
        self::expectException(InvalidArgumentException::class);
        CapCode::fromString((string) $testValue);
    }
}
