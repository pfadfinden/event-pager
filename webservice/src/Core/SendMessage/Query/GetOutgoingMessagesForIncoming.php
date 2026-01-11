<?php

declare(strict_types=1);

namespace App\Core\SendMessage\Query;

use App\Core\Contracts\Bus\Query;
use App\Core\SendMessage\ReadModel\OutgoingMessageDetail;

/**
 * @implements Query<iterable<OutgoingMessageDetail>>
 */
readonly class GetOutgoingMessagesForIncoming implements Query
{
    public function __construct(
        public string $incomingMessageId,
    ) {
    }
}
