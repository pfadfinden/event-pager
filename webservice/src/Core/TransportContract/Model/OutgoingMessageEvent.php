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
    public Ulid $outgoingMessageId;

    public Instant $at;

    public OutgoingMessageStatus $status;

    public static function queued(
        Ulid $outgoingMessageId,
    ): self {
        return new self($outgoingMessageId, Instant::now(), OutgoingMessageStatus::QUEUED);
    }

    public static function failedToQueue(
        Ulid $outgoingMessageId,
    ): self {
        return new self($outgoingMessageId, Instant::now(), OutgoingMessageStatus::ERROR);
    }

    public static function failedToTransmit(
        Ulid $outgoingMessageId,
    ): self {
        return new self($outgoingMessageId, Instant::now(), OutgoingMessageStatus::ERROR);
    }

    public static function transmitted(
        Ulid $outgoingMessageId,
    ): self {
        return new self($outgoingMessageId, Instant::now(), OutgoingMessageStatus::TRANSMITTED);
    }

    private function __construct(
        Ulid $outgoingMessageId,
        Instant $at,
        OutgoingMessageStatus $status,
    ) {
        $this->outgoingMessageId = $outgoingMessageId;
        $this->status = $status;
        $this->at = $at;
    }
}
