<?php

declare(strict_types=1);

namespace App\Core\IntelPage\Events;

/**
 * The responsible transport failed to transmit the referenced outgoing message.
 *
 * Not recoverable by this transport.
 *
 * TODO move to other module
 */
final readonly class OutgoingMessageTransmissionFailed
{
    public function __construct(
        public string $messageId,
        public string $failedReason,
    ) {
    }
}
