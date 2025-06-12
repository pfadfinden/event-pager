<?php

declare(strict_types=1);

namespace App\Tests\Core\TransportManager\Query;

use App\Core\TransportManager\Query\AllTransports;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(AllTransports::class)]
#[Group('unit')]
final class AllTransportsTest extends TestCase
{
    public function testDefaultIsNotFiltered(): void
    {
        $sut = AllTransports::withoutFilter();

        self::assertNull($sut->filterEnabledStatus);
    }

    public function testFilterOnlyEnabled(): void
    {
        $sut = AllTransports::thatAreEnabled();

        self::assertTrue($sut->filterEnabledStatus);
    }

    public function testFilterOnlyDisabled(): void
    {
        $sut = AllTransports::thatAreDisabled();

        self::assertFalse($sut->filterEnabledStatus);
    }
}
