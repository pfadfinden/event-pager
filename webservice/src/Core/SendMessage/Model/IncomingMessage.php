<?php

declare(strict_types=1);

namespace App\Core\SendMessage\Model;

use App\Infrastructure\Persistence\DoctrineORM\Type\InstantType;
use App\Infrastructure\Persistence\DoctrineORM\Type\UlidArrayType;
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
        Ulid $by,
        array $to,
        string $content,
        int $priority,
    ): self {
        return new self(
            Ulid::generate(),
            Instant::now(),
            $by,
            $to,
            $content,
            $priority,
        );
    }

    /**
     * @param array<Ulid> $to
     */
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: UlidType::NAME)]
        public string $messageId,
        #[ORM\Column(type: InstantType::NAME)]
        public Instant $sentOn,
        #[ORM\Column(type: UlidType::NAME)]
        public Ulid $by,
        #[ORM\Column(name: 'sentTo', type: UlidArrayType::NAME)]
        public array $to,
        #[ORM\Column]
        public string $content,
        #[ORM\Column]
        public int $priority,
    ) {
    }
}
