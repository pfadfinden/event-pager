<?php

declare(strict_types=1);

namespace App\Core\IntelPage\Events;

/**
 * The responsible transport successfully transmitted the outgoing message.
 *
 * The transport was not able to determine if the message was delivered (yet).
 *
 * TODO move to other module
 */
final readonly class OutgoingMessageTransmitted
{
    public function __construct(public string $messageId)
    {
    }
}
