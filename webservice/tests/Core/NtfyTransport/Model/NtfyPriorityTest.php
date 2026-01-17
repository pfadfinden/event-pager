<?php

declare(strict_types=1);

namespace App\Tests\Core\NtfyTransport\Model;

use App\Core\NtfyTransport\Model\NtfyPriority;
use App\Core\TransportContract\Model\Priority;
use Iterator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(NtfyPriority::class)]
#[Group('unit')]
final class NtfyPriorityTest extends TestCase
{
    /**
     * @return Iterator<string, array{Priority, NtfyPriority}>
     */
    public static function providePriorityMappings(): Iterator
    {
        yield 'URGENT maps to MAX' => [Priority::URGENT, NtfyPriority::MAX];
        yield 'HIGH maps to HIGH' => [Priority::HIGH, NtfyPriority::HIGH];
        yield 'DEFAULT maps to DEFAULT' => [Priority::DEFAULT, NtfyPriority::DEFAULT];
        yield 'LOW maps to LOW' => [Priority::LOW, NtfyPriority::LOW];
        yield 'MIN maps to MIN' => [Priority::MIN, NtfyPriority::MIN];
    }

    #[DataProvider('providePriorityMappings')]
    public function testFromPriorityMapsCorrectly(Priority $appPriority, NtfyPriority $expectedNtfyPriority): void
    {
        self::assertSame($expectedNtfyPriority, NtfyPriority::fromPriority($appPriority));
    }

    public function testNtfyPriorityValues(): void
    {
        self::assertSame(5, NtfyPriority::MAX->value);
        self::assertSame(4, NtfyPriority::HIGH->value);
        self::assertSame(3, NtfyPriority::DEFAULT->value);
        self::assertSame(2, NtfyPriority::LOW->value);
        self::assertSame(1, NtfyPriority::MIN->value);
    }
}
