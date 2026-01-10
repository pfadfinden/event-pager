<?php

declare(strict_types=1);

namespace App\Core\IntelPage\Query;

use App\Core\Contracts\Bus\Query;

/**
 * @implements Query<int>
 */
final readonly class CountOfChannel implements Query
{
    public static function withoutFilter(): self
    {
        return new self();
    }

    public static function withTextSearch(string $textFilter): self
    {
        return new self($textFilter);
    }

    private function __construct(
        public ?string $textFilter = null,
    ) {
    }
}
