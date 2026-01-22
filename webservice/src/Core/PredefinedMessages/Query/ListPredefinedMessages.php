<?php

declare(strict_types=1);

namespace App\Core\PredefinedMessages\Query;

use App\Core\Contracts\Bus\Query;
use App\Core\PredefinedMessages\ReadModel\PredefinedMessageListEntry;

/**
 * @implements Query<iterable<PredefinedMessageListEntry>>
 */
final readonly class ListPredefinedMessages implements Query
{
    public const int DEFAULT_PAGE_LENGTH = 25;

    public static function all(?string $textFilter = null, ?int $page = null, ?int $perPage = null): self
    {
        return new self($textFilter, $page, $perPage);
    }

    public static function withoutFilter(?int $page = null, ?int $perPage = null): self
    {
        return new self(null, $page, $perPage);
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
