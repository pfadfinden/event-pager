<?php

declare(strict_types=1);

namespace App\Core\SendMessage\Query;

use App\Core\Contracts\Bus\Query;
use App\Core\SendMessage\ReadModel\MessageHistoryEntry;

/**
 * @implements Query<iterable<MessageHistoryEntry>>
 */
readonly class ListMessageHistory implements Query
{
    public const DEFAULT_PAGE_LENGTH = 10;

    public static function forUser(string $userId, ?string $searchText = null, ?int $page = null, ?int $perPage = null): self
    {
        return new self(new MessageHistoryFilter($searchText, $userId, $page, $perPage));
    }

    public static function all(?string $searchText = null, ?int $page = null, ?int $perPage = null): self
    {
        return new self(new MessageHistoryFilter($searchText, null, $page, $perPage));
    }

    private function __construct(
        public MessageHistoryFilter $filter,
    ) {
    }
}
