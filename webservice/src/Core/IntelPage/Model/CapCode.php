<?php

declare(strict_types=1);

namespace App\Core\IntelPage\Model;

use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

#[ORM\Embeddable]
readonly class CapCode
{
    #[ORM\Column]
    private int $code;

    private const int CAP_CODE_MAX = 9999;

    private const int CAP_CODE_MIN = 1;

    public function isInBounds(int $code): bool
    {
        return ($code >= self::CAP_CODE_MIN) && ($code <= self::CAP_CODE_MAX);
    }

    private function __construct(int $code)
    {
        if (!$this->isInBounds($code)) {
            throw new InvalidArgumentException('Cap code out of bounds!');
        }

        $this->code = $code;
    }

    public static function fromString(string $cap): self
    {
        return new self((int) $cap);
    }

    public static function fromInt(int $cap): self
    {
        return new self($cap);
    }

    public function getCode(): int
    {
        return $this->code;
    }
}
