<?php

declare(strict_types=1);

namespace App\Core\SendMessage\Query;

use App\Core\Contracts\Bus\Query;

/**
 * @implements Query<int>
 */
readonly class CountMessageHistory implements Query
{
    public static function forUser(string $userId, ?string $searchText = null): self
    {
        return new self(new MessageHistoryFilter($searchText, $userId));
    }

    public static function all(?string $searchText = null): self
    {
        return new self(new MessageHistoryFilter(searchText: $searchText));
    }

    private function __construct(
        public MessageHistoryFilter $filter,
    ) {
    }
}
