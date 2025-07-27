<?php

declare(strict_types=1);

namespace App\Core\IntelPage\Query;

use App\Core\Contracts\Bus\Query;

/**
 * @implements Query<\App\Core\IntelPage\ReadModel\CapAssignment>
 */
final readonly class CapAssignments implements Query
{
    public static function forPagerWithId(string $id): self
    {
        return new self($id);
    }

    private function __construct(public string $id)
    {
    }
}
