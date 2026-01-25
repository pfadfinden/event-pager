<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\TransportContract\Model;

use App\Core\TransportContract\Model\Priority;
use Iterator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Priority::class)]
final class PriorityTest extends TestCase
{
    /**
     * @return Iterator<string, array{Priority, Priority, bool}>
     */
    public static function provideHigherOrEqualsSamples(): Iterator
    {
        yield 'high compared to low' => [Priority::HIGH, Priority::LOW, true];
        yield 'low compared to high' => [Priority::LOW, Priority::HIGH, false];
        yield 'urgent compared to high' => [Priority::URGENT, Priority::HIGH, true];
        yield 'min compared to low' => [Priority::MIN, Priority::LOW, false];
        yield 'default compared to low' => [Priority::DEFAULT, Priority::LOW, true];
    }

    #[DataProvider('provideHigherOrEqualsSamples')]
    public function testHigherOrEqualWorks(Priority $a, Priority $b, bool $expected): void
    {
        self::assertSame($expected, $a->isHigherOrEqual($b));
    }
}
