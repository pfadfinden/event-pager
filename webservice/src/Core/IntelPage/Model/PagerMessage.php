<?php

declare(strict_types=1);

namespace App\Core\IntelPage\Model;

use Brick\DateTime\Instant;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity]
class PagerMessage
{
    #[ORM\Id]
    #[ORM\Column(type: 'ulid')]
    private readonly Ulid $id;

    #[ORM\Embedded]
    private readonly CapCode $cap;

    #[ORM\Column(length: 512)]
    private readonly string $message;

    #[ORM\Column(type: 'instant')]
    private readonly Instant $queuedOn;

    #[ORM\Column(type: 'instant', nullable: true)]
    private ?Instant $transmittedOn = null;

    #[ORM\Column]
    private readonly int $priority;

    #[ORM\Column]
    private int $attemptedToSend = 0;

    public static function new(Ulid $id, CapCode $cap, string $message, int $priority): self
    {
        return new self(
            $id,
            $cap,
            $message,
            $priority,
            Instant::now()
        );
    }

    public function __construct(
        Ulid $id, CapCode $cap, string $message, int $priority, Instant $queuedOn)
    {
        $this->id = $id;
        $this->cap = $cap;
        $this->message = $message;
        $this->priority = $priority;
        $this->queuedOn = $queuedOn;
    }

    public function getId(): Ulid
    {
        return $this->id;
    }

    public function getCap(): CapCode
    {
        return $this->cap;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function markSend(): void
    {
        $this->transmittedOn = Instant::now();
    }

    public function failedToSend(): void
    {
        ++$this->attemptedToSend;
    }

    public function getAttemptedToSend(): int
    {
        return $this->attemptedToSend;
    }

    public function getTransmittedOn(): ?Instant
    {
        return $this->transmittedOn;
    }
}
