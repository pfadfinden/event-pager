<?php

declare(strict_types=1);

namespace App\Core\TransportContract\Model;

use Symfony\Component\Uid\Ulid;

/**
 * Contract for messages pushed to a transport, to keep the transport module low-level.
 */
interface Message
{
    public Ulid $messageId { get; }
    public string $body { get; }
    public Priority $priority { get; }
}
