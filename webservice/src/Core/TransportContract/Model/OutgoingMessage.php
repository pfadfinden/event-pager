<?php

declare(strict_types=1);

namespace App\Core\TransportContract\Model;

use Symfony\Component\Uid\Ulid;

/**
 * Parameter object for Transport::send() method.
 *
 * @see Transport::send()
 */
readonly class OutgoingMessage
{
    public static function for(MessageRecipient $recipient, Message $incomingMessage): self
    {
        return new self(
            Ulid::fromString(Ulid::generate()),
            $recipient,
            $incomingMessage,
        );
    }

    private function __construct(
        public Ulid $id,
        public MessageRecipient $recipient,
        public Message $incomingMessage,
    ) {
    }
}
