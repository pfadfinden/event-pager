<?php

declare(strict_types=1);

namespace App\Core\TransportContract\Model;

use Brick\DateTime\Instant;
use Symfony\Component\Uid\Ulid;

/**
 * Event raised when a new outgoing message is created for a recipient.
 *
 * This event captures the recipientId before the message is passed to transports,
 * establishing the association between the outgoing message and its recipient.
 */
readonly class NewOutgoingMessageInitiated
{
    public static function for(OutgoingMessage $message, bool $failed = false): self
    {
        return new self(
            $message->incomingMessage->messageId,
            $message->id,
            $message->recipient->getId(),
            Instant::now(),
            $message->transport,
            $failed
        );
    }

    public function __construct(
        public Ulid $incomingMessageId,
        public Ulid $outgoingMessageId,
        public Ulid $recipientId,
        public Instant $at,
        public string $transportKey,
        public bool $failed = false,
    ) {
    }
}
