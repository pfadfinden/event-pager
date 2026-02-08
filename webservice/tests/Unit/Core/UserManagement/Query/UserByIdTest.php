<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\UserManagement\Query;

use App\Core\UserManagement\Query\UserById;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(UserById::class)]
#[Group('unit')]
final class UserByIdTest extends TestCase
{
    public function testCanBeCreatedWithId(): void
    {
        $query = UserById::withId(42);

        self::assertSame(42, $query->id);
    }

    public function testCanBeCreatedWithDifferentIds(): void
    {
        $query1 = UserById::withId(1);
        $query2 = UserById::withId(999);

        self::assertSame(1, $query1->id);
        self::assertSame(999, $query2->id);
    }

    public function testIdPropertyIsPubliclyReadable(): void
    {
        $query = UserById::withId(123);

        self::assertIsInt($query->id);
    }
}
