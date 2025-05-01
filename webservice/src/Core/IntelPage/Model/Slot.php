<?php

declare(strict_types=1);

namespace App\Core\IntelPage\Model;

use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use function sprintf;

#[ORM\Embeddable]
readonly class Slot
{
    #[ORM\Column]
    private int $slot;

    public const int SLOT_MAX = 7;
    public const int SLOT_MIN = 0;

    public function isInBounds(int $slot): bool
    {
        return ($slot >= self::SLOT_MIN) && ($slot <= self::SLOT_MAX);
    }

    public static function fromString(string $cap): self
    {
        return new self((int) $cap);
    }

    public static function fromInt(int $cap): self
    {
        return new self($cap);
    }

    public function __construct(int $slot)
    {
        if (!$this->isInBounds($slot)) {
            throw new InvalidArgumentException(sprintf('Slot value %d out of bounds!', $slot));
        }
        $this->slot = $slot;
    }

    public function getSlot(): int
    {
        return $this->slot;
    }
}
