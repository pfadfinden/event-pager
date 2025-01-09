<?php

namespace App\Core\IntelPage\Model;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class IndividualCapAssignment extends AbstractCapAssignment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Embedded]
    private readonly CapCode $capCode;

    #[ORM\Column]
    private bool $audible;

    #[ORM\Column]
    private bool $vibration;

    public function __construct(bool $audible, bool $vibration, CapCode $capCode)
    {
        $this->audible = $audible;
        $this->vibration = $vibration;
        $this->capCode = $capCode;
    }

    public function getCapCode(): CapCode
    {
        return $this->capCode;
    }

    public function setCapCode(CapCode $capCode): static
    {
        $this->capCode = $capCode;

        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function isAudible(): bool
    {
        return $this->audible;
    }

    public function setAudible(bool $audible): static
    {
        $this->audible = $audible;

        return $this;
    }

    public function isVibration(): bool
    {
        return $this->vibration;
    }

    public function setVibration(bool $vibration): static
    {
        $this->vibration = $vibration;

        return $this;
    }
}
