<?php

declare(strict_types=1);

namespace App\Core\MessageRecipient\Query;

use App\Core\Contracts\Bus\Query;
use App\Core\MessageRecipient\ReadModel\RecipientDetail;

/**
 * @implements Query<RecipientDetail|null>
 */
final readonly class MessageRecipientById implements Query
{
    public static function withId(string $id): self
    {
        return new self($id);
    }

    private function __construct(public string $id)
    {
    }
}
