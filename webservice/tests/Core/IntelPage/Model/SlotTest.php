<?php

declare(strict_types=1);

namespace App\Tests\Core\IntelPage\Model;

use App\Core\IntelPage\Model\Slot;
use InvalidArgumentException;
use Iterator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(Slot::class)]
#[Group('unit')]
final class SlotTest extends TestCase
{
    /**
     * @return Iterator<string, list<int>>
     */
    public static function validIntProvider(): Iterator
    {
        yield 'lower bound' => [0];
        yield 'upper bound' => [7];
    }

    /**
     * @return Iterator<string, list<int>>
     */
    public static function invalidIntProvider(): Iterator
    {
        yield 'lower bound' => [-1];
        yield 'upper bound' => [8];
    }

    #[DataProvider('validIntProvider')]
    public function testCanBeCreatedByInt(int $testValue): void
    {
        $cc = Slot::fromInt($testValue);
        self::assertSame($cc->getSlot(), $testValue);
    }

    #[DataProvider('invalidIntProvider')]
    public function testCreateInvalidSlot(int $testValue): void
    {
        self::expectException(InvalidArgumentException::class);
        Slot::fromInt($testValue);
    }

    #[DataProvider('validIntProvider')]
    public function testCanBeCreatedFromString(int $testValue): void
    {
        $cc = Slot::fromString((string) $testValue);
        self::assertSame($cc->getSlot(), $testValue);
    }

    #[DataProvider('invalidIntProvider')]
    public function testCreateInvalidSlotFromString(int $testValue): void
    {
        self::expectException(InvalidArgumentException::class);
        Slot::fromString((string) $testValue);
    }
}
