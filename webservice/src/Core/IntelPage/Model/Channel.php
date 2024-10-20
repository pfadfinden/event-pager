<?php

declare(strict_types=1);

namespace App\Core\IntelPage\Model;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity]
class Channel
{
    #[ORM\Id]
    #[ORM\Column(type: 'ulid')]
    protected readonly Ulid $id;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Embedded]
    private CapCode $capCode;

    #[ORM\Column]
    private bool $audible;

    #[ORM\Column]
    private bool $vibration;

    public function __construct(Ulid $id, string $name, CapCode $capCode, bool $audible = true, bool $vibration = true)
    {
        $this->id = $id;
        $this->name = $name;
        $this->capCode = $capCode;
        $this->audible = $audible;
        $this->vibration = $vibration;
    }

    public function getCapCode(): CapCode
    {
        return $this->capCode;
    }

    public function setCapCode(CapCode $capCode): void
    {
        $this->capCode = $capCode;
    }

    public function getId(): Ulid
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }
}
