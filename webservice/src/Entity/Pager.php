<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Pager
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(length: 255)]
    private string $label;

    #[ORM\Column]
    private int $number;

    #[ORM\OneToMany(
        targetEntity: AbstractCapAssignment::class,
        mappedBy: 'slots',
        cascade: ['persist', 'remove'],
        orphanRemoval: true,
        indexBy: 'slot'
    )]
    private Collection $slots;

    public function __construct(
        string $label,
        int $number,
        ArrayCollection $slots = new ArrayCollection(),
    ) {
        $this->slots = $slots;
        $this->label = $label;
        $this->number = $number;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    private function isInSlotBounds(int $slot): bool
    {
        return ($slot >= 0) && ($slot < 6);
    }

    public function getCapAssignment(int $atSlot): AbstractCapAssignment
    {
        if (!$this->isInSlotBounds($atSlot)) {
            throw new \InvalidArgumentException('Trying to access out of bounds slot!');
        }

        return $this->slots->get($atSlot);
    }

    public function getCapAssignments(): iterable
    {
        return $this->slots->toArray();
    }

    public function assignCap(int $atSlot, AbstractCapAssignment $assignment): static
    {
        if (!$this->isInSlotBounds($atSlot)) {
            throw new \InvalidArgumentException('Trying to access out of bounds slot!');
        }

        $this->slots->set($atSlot, $assignment);

        return $this;
    }

    public function setLabel(string $label): static
    {
        if (strlen($label) > 255 || 0 === strlen($label)) {
            throw new \InvalidArgumentException('The length of the new label must be from 0 to 255 characters!');
        }

        $this->label = $label;

        return $this;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function setNumber(int $number): static
    {
        $this->number = $number;

        return $this;
    }
}
