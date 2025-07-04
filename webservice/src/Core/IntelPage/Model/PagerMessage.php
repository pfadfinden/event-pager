<?php

declare(strict_types=1);

namespace App\Core\IntelPage\Model;

use Brick\DateTime\Instant;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity]
class PagerMessage
{
    public const int MAX_LENGTH = 512;

    #[ORM\Id]
    #[ORM\Column(type: 'ulid')]
    private readonly Ulid $id;

    #[ORM\Column]
    private readonly string $transport;

    #[ORM\Embedded]
    private readonly CapCode $cap;

    #[ORM\Column(length: self::MAX_LENGTH)]
    private readonly string $message;

    #[ORM\Column(type: 'instant')]
    private readonly Instant $queuedOn;

    #[ORM\Column(type: 'instant', nullable: true)]
    private ?Instant $transmittedOn = null;

    #[ORM\Column]
    private readonly int $priority;

    #[ORM\Column]
    private int $attemptedToSend = 0;

    public static function new(Ulid $id, string $transport, CapCode $cap, string $message, int $priority): self
    {
        if (str_contains($message, "\r")) {
            $message = str_replace("\r", ' ', $message);
        }

        return new self(
            $id,
            $transport,
            $cap,
            $message,
            $priority,
            Instant::now()
        );
    }

    public function __construct(
        Ulid $id, string $transport, CapCode $cap, string $message, int $priority, Instant $queuedOn)
    {
        $this->id = $id;
        $this->transport = $transport;
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
