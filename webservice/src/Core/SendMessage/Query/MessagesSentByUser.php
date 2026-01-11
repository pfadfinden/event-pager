<?php

declare(strict_types=1);

namespace App\Core\SendMessage\Query;

use App\Core\Contracts\Bus\Query;

/**
 * @implements Query<array<int, \App\Core\SendMessage\ReadModel\IncomingMessageStatus>>
 */
readonly class MessagesSentByUser implements Query
{
    public function __construct(public string $sentBy, public MessageFilter $filter)
    {
    }
}
