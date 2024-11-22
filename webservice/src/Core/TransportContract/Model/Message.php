<?php

declare(strict_types=1);

namespace App\Core\TransportContract\Model;

use Symfony\Component\Uid\Ulid;

/**
 * Contract for messages pushed to a transport, to keep the transport module low-level.
 *
 * @property Ulid     $messageId
 * @property string   $body
 * @property Priority $priority
 */
interface Message
{
    // FOR PHP 8.4:
    // public Ulid $messageId { get; }
    // public string $body { get; }
    // public Priority $priority { get; }
}
