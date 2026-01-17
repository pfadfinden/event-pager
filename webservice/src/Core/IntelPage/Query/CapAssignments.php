<?php

declare(strict_types=1);

namespace App\Core\IntelPage\Query;

use App\Core\Contracts\Bus\Query;
use App\Core\IntelPage\ReadModel\CapAssignment;

/**
 * @implements Query<CapAssignment>
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
