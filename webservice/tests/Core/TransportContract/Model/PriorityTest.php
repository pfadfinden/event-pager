<?php

declare(strict_types=1);

namespace App\Tests\Core\TransportContract\Model;

use App\Core\TransportContract\Model\Priority;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(Priority::class)]
#[Group('unit')]
final class PriorityTest extends TestCase
{
    /**
     * @return array<string, array{Priority, Priority, bool}>
     */
    public static function provideHigherOrEqualsSamples(): array
    {
        return [
            'high compared to low' => [Priority::HIGH, Priority::LOW, true],
            'low compared to high' => [Priority::LOW, Priority::HIGH, false],
            'urgent compared to high' => [Priority::URGENT, Priority::HIGH, true],
            'min compared to low' => [Priority::MIN, Priority::LOW, false],
            'default compared to low' => [Priority::DEFAULT, Priority::LOW, true],
        ];
    }

    #[DataProvider('provideHigherOrEqualsSamples')]
    public function testHigherOrEqualWorks(Priority $a, Priority $b, bool $expected): void
    {
        self::assertSame($expected, $a->isHigherOrEqual($b));
    }
}
