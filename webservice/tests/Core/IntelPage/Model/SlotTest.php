<?php

declare(strict_types=1);

namespace App\Tests\Core\IntelPage\Model;

use App\Core\IntelPage\Model\Slot;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(Slot::class)]
#[Group('unit')]
final class SlotTest extends TestCase
{
    /**
     * @return array<string, list<int>>
     */
    public static function validIntProvider(): array
    {
        return [
            'lower bound' => [0],
            'upper bound' => [7],
        ];
    }

    /**
     * @return array<string, list<int>>
     */
    public static function invalidIntProvider(): array
    {
        return [
            'lower bound' => [-1],
            'upper bound' => [8],
        ];
    }

    #[DataProvider('validIntProvider')]
    public function testCanBeCreatedByInt(int $testValue): void
    {
        $cc = Slot::fromInt($testValue);
        self::assertTrue($testValue === $cc->getSlot());
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
        self::assertTrue($testValue === $cc->getSlot());
    }

    #[DataProvider('invalidIntProvider')]
    public function testCreateInvalidSlotFromString(int $testValue): void
    {
        self::expectException(InvalidArgumentException::class);
        Slot::fromString((string) $testValue);
    }
}
