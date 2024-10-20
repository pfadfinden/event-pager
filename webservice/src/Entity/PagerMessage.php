<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class PagerMessage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Embedded]
    private CapCode $cap;

    #[ORM\Column(length: 255)]
    private string $message;

    public function getId(): int
    {
        return $this->id;
    }

    public function getCap(): CapCode
    {
        return $this->cap;
    }

    public function setCap(CapCode $cap): static
    {
        $this->cap = $cap;

        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }
}
