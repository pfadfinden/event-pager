<?php

declare(strict_types=1);

namespace App\Tests\Core\TransportManager\Query;

use App\Core\TransportManager\Query\AllPager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(AllPager::class)]
#[Group('unit')]
final class AllTransportsTest extends TestCase
{
    public function testDefaultIsNotFiltered(): void
    {
        $sut = AllPager::withoutFilter();

        self::assertNull($sut->filterEnabledStatus);
    }

    public function testFilterOnlyEnabled(): void
    {
        $sut = AllPager::thatAreEnabled();

        self::assertTrue($sut->filterEnabledStatus);
    }

    public function testFilterOnlyDisabled(): void
    {
        $sut = AllPager::thatAreDisabled();

        self::assertFalse($sut->filterEnabledStatus);
    }
}
