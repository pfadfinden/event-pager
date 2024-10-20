<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
class CapCode
{
    #[ORM\Column(type: 'integer')]
    private int $code;

    private static int $CAP_CODE_MAX = 9;
    private static int $CAP_CODE_MIN = 1;

    public function isInBounds(int $code): bool
    {
        return ($code >= self::$CAP_CODE_MIN) && ($code <= self::$CAP_CODE_MAX);
    }

    public function __construct(int $code)
    {
        if (!$this->isInBounds($code)) {
            throw new \InvalidArgumentException('Cap code out of bounds!');
        }

        $this->code = $code;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function setCode(int $code): static
    {
        if (!$this->isInBounds(0)) {
            throw new \InvalidArgumentException('Cap code out of bounds!');
        }
        $this->code = $code;

        return $this;
    }
}
