<?php

declare(strict_types=1);

namespace App\Core\UserManagement\Query;

use App\Core\Contracts\Bus\Query;
use App\Core\UserManagement\ReadModel\UserDetail;

/**
 * @implements Query<UserDetail|null>
 */
final readonly class UserById implements Query
{
    public static function withId(int $id): self
    {
        return new self($id);
    }

    private function __construct(public int $id)
    {
    }
}
