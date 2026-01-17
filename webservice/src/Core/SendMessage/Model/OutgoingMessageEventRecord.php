<?php

declare(strict_types=1);

namespace App\Core\SendMessage\Model;

use App\Core\TransportContract\Model\OutgoingMessageStatus;
use App\Infrastructure\Persistence\DoctrineORM\Type\InstantType;
use Brick\DateTime\Instant;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid;
use const STR_PAD_LEFT;

/**
 * Persisted record of an OutgoingMessageEvent.
 *
 * This entity captures status changes for outgoing messages as they progress
 * through the transport system. Each status change creates a new record,
 * allowing full history tracking.
 *
 * The ID is a lexicographically sortable string combining:
 * - Zero-padded epoch milliseconds (for chronological sorting)
 * - A short hash of outgoing message ID + status + random bytes (for uniqueness)
 */
#[ORM\Entity(readOnly: true)]
#[ORM\Index(name: 'idx_omer_incoming', columns: ['incoming_message_id'])]
#[ORM\Index(name: 'idx_omer_outgoing', columns: ['outgoing_message_id'])]
readonly class OutgoingMessageEventRecord
{
    private const int TIMESTAMP_PADDING = 15;

    public static function create(
        Ulid $outgoingMessageId,
        Instant $recordedAt,
        OutgoingMessageStatus $status,
        ?Ulid $incomingMessageId = null,
        ?Ulid $recipientId = null,
    ): self {
        return new self(
            self::generateId($outgoingMessageId, $recordedAt, $status),
            $outgoingMessageId,
            $recordedAt,
            $status,
            $incomingMessageId,
            $recipientId,
        );
    }

    private static function generateId(
        Ulid $outgoingMessageId,
        Instant $recordedAt,
        OutgoingMessageStatus $status,
    ): string {
        $epochMillis = $recordedAt->getEpochSecond() * 1000
            + intdiv($recordedAt->getNano(), 1_000_000);
        $timestampPart = str_pad((string) $epochMillis, self::TIMESTAMP_PADDING, '0', STR_PAD_LEFT);

        $randomBytes = random_bytes(8);
        $hashInput = $outgoingMessageId->toRfc4122().$status->value.$randomBytes;
        $hashPart = substr(hash('xxh3', $hashInput), 0, 12);

        return $timestampPart.'-'.$hashPart;
    }

    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: Types::STRING, length: 28)]
        public string $id,
        #[ORM\Column(type: UlidType::NAME)]
        public Ulid $outgoingMessageId,
        #[ORM\Column(type: InstantType::NAME)]
        public Instant $recordedAt,
        #[ORM\Column(type: Types::INTEGER, enumType: OutgoingMessageStatus::class)]
        public OutgoingMessageStatus $status,
        #[ORM\Column(type: UlidType::NAME, nullable: true)]
        public ?Ulid $incomingMessageId = null,
        #[ORM\Column(type: UlidType::NAME, nullable: true)]
        public ?Ulid $recipientId = null,
    ) {
    }
}
