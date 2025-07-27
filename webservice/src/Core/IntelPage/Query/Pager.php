<?php

declare(strict_types=1);

namespace App\Core\IntelPage\Query;

use App\Core\Contracts\Bus\Query;

/**
 * @implements Query<\App\Core\IntelPage\ReadModel\Pager>
 */
final readonly class Pager implements Query
{
    public static function withId(string $id): self
    {
        return new self($id);
    }

    private function __construct(public string $id)
    {
    }
}
