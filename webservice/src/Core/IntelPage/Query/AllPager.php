<?php

declare(strict_types=1);

namespace App\Core\IntelPage\Query;

use App\Core\Contracts\Bus\Query;
use App\Core\IntelPage\ReadModel\Pager;

/**
 * @implements Query<iterable<Pager>>
 */
final readonly class AllPager implements Query
{
    public static function withoutFilter(): self
    {
        return new self();
    }

    private function __construct()
    {
    }
}
