<?php

namespace App\Core\SendMessage\Query;

use App\Core\Bus\Query;

/**
 * @implements Query<array<int, \App\Core\SendMessage\ReadModel\SendMessageStatus>>
 */
readonly class MessagesSendByUser implements Query
{
    public function __construct(public string $sendBy, public MessageFilter $filter)
    {
    }
}
