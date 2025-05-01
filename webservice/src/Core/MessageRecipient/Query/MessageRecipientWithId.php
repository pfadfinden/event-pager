<?php

declare(strict_types=1);

namespace App\Core\MessageRecipient\Query;

use App\Core\Contracts\Bus\Query;
use App\Core\MessageRecipient\ReadModel\RecipientListEntry;

/**
 * @implements Query<RecipientListEntry>
 */
final readonly class MessageRecipientWithId implements Query
{
    public function __construct(public string $id)
    {
    }
}
