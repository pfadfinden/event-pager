<?php

declare(strict_types=1);

namespace App\Core\TransportContract\Model;

use Brick\DateTime\Instant;
use Symfony\Component\Uid\Ulid;

/**
 * While processing an outgoing message, it reaches several states.
 * When reaching one, an OutgoingMessageEvent should be raised.
 *
 * @see OutgoingMessageStatus
 */
readonly class OutgoingMessageEvent
{
    public Ulid $incomingMessageId;

    public Ulid $outgoingMessageId;

    public Instant $at;

    public OutgoingMessageStatus $status;

    public static function queued(
        Ulid $incomingMessageId,
        Ulid $outgoingMessageId,
    ): self {
        return new self($incomingMessageId, $outgoingMessageId, Instant::now(), OutgoingMessageStatus::QUEUED);
    }

    public static function transmitted(
        Ulid $incomingMessageId,
        Ulid $outgoingMessageId,
    ): self {
        return new self($incomingMessageId, $outgoingMessageId, Instant::now(), OutgoingMessageStatus::TRANSMITTED);
    }

    private function __construct(
        Ulid $incomingMessageId,
        Ulid $outgoingMessageId,
        Instant $at,
        OutgoingMessageStatus $status,
    ) {
        $this->incomingMessageId = $incomingMessageId;
        $this->outgoingMessageId = $outgoingMessageId;
        $this->status = $status;
        $this->at = $at;
    }
}
