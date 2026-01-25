<?php

declare(strict_types=1);

namespace App\Core\SendMessage\ReadModel;

/**
 * DTO for expanded outgoing message detail in message history.
 */
readonly class OutgoingMessageDetail
{
    public function __construct(
        public string $outgoingMessageId,
        public string $recipientId,
        public string $recipientName,
        public string $transportKey,
        public string $latestStatus,
        public string $latestStatusAt,
    ) {
    }
}
