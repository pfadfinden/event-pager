<?php

declare(strict_types=1);

namespace App\Core\PredefinedMessages\Query;

use App\Core\Contracts\Bus\Query;

/**
 * @implements Query<int>
 */
final readonly class CountPredefinedMessages implements Query
{
    public static function all(?string $textFilter = null): self
    {
        return new self($textFilter);
    }

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
