<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'discr', type: 'string')]
#[ORM\DiscriminatorMap([
    'nocapassignment' => NoCapAssignment::class,
    'individualcapassignment' => IndividualCapAssignment::class,
    'channelcapassignment' => ChannelCapAssignment::class,
])]
abstract class AbstractCapAssignment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column]
    private int $slot;

    #[ORM\ManyToOne(targetEntity: Pager::class, inversedBy: 'slots')]
    #[ORM\JoinColumn(name: 'pager_id', referencedColumnName: 'id')]
    private Pager $pager;

    #[ORM\Column]
    private bool $audible;

    #[ORM\Column]
    private bool $vibration;

    public function getId(): int
    {
        return $this->id;
    }

    public function getSlot(): int
    {
        return $this->slot;
    }

    public function setSlot(int $slot): static
    {
        $this->slot = $slot;

        return $this;
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
