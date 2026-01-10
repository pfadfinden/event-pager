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
    public const DEFAULT_PAGE_LENGTH = 25;

    public static function withoutFilter(?int $page = null, ?int $perPage = null): self
    {
        return new self(page: $page, perPage: $perPage);
    }

    public static function withTextSearch(string $textFilter, ?int $page = null, ?int $perPage = null): self
    {
        return new self($textFilter, $page, $perPage);
    }

    private function __construct(
        public ?string $textFilter = null,
        public ?int $page = null,
        public ?int $perPage = null,
    ) {
    }
}
