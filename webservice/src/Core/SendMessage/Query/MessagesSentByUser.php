<?php

declare(strict_types=1);

namespace App\Core\SendMessage\Query;

use App\Core\Contracts\Bus\Query;
use App\Core\SendMessage\ReadModel\IncomingMessageStatus;

/**
 * @implements Query<array<int, IncomingMessageStatus>>
 */
readonly class MessagesSentByUser implements Query
{
    public function __construct(public string $sentBy, public MessageFilter $filter)
    {
    }
}
