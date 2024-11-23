<?php

declare(strict_types=1);

namespace App\Core\SendMessage\Model;

use Brick\DateTime\Instant;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid;

/**
 * The IncomingMessage entity documents all messages recieved by the system.
 *
 * It is readonly since all states of the message can be computed from the outgoing message table
 */
#[ORM\Entity(readOnly: true)]
readonly class IncomingMessage
{
    /**
     * @param array<Ulid> $to
     */
    public static function new(
        Ulid $sendBy,
        array $to,
        string $content,
        int $priority,
    ): self {
        return new self(
            Ulid::generate(),
            Instant::now(),
            $sendBy,
            $to,
            $content,
            $priority,
        );
    }

    /**
     * @param array<Ulid> $sendTo
     */
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: UlidType::NAME)]
        public string $messageId,
        #[ORM\Column]
        public Instant $sendOn,
        #[ORM\Column]
        public Ulid $sendBy,
        #[ORM\Column]
        public array $sendTo,
        #[ORM\Column]
        public string $content,
        #[ORM\Column]
        public int $priority,
    ) {
    }
}
