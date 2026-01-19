<?php

declare(strict_types=1);

namespace App\Core\PredefinedMessages\Query;

use App\Core\Contracts\Bus\Query;
use App\Core\PredefinedMessages\ReadModel\PredefinedMessageListEntry;

/**
 * @implements Query<iterable<PredefinedMessageListEntry>>
 */
final readonly class ListFavoritePredefinedMessages implements Query
{
    public static function topNine(): self
    {
        return new self(9);
    }

    public static function withLimit(int $limit): self
    {
        return new self($limit);
    }

    private function __construct(public int $limit)
    {
    }
}
