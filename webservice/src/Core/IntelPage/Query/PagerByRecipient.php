<?php

declare(strict_types=1);

namespace App\Core\IntelPage\Query;

use App\Core\Contracts\Bus\Query;
use App\Core\IntelPage\Model\Pager;

/**
 * @implements Query<Pager|null>
 */
final readonly class PagerByRecipient implements Query
{
    public static function withId(string $id): self
    {
        return new self($id);
    }

    private function __construct(public string $recipientId)
    {
    }
}
