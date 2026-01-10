<?php

declare(strict_types=1);

namespace App\Core\IntelPage\Query;

use App\Core\Contracts\Bus\Query;
use App\Core\IntelPage\ReadModel\PagerInChannel;

/**
 * @implements Query<iterable<PagerInChannel>>
 */
final readonly class AllPagerWithChannel implements Query
{
    public static function withId(string $channelId): self
    {
        return new self($channelId);
    }

    private function __construct(public string $channelId)
    {
    }
}
