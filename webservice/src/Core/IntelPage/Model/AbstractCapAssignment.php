<?php

declare(strict_types=1);

namespace App\Core\IntelPage\Model;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;

#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\Entity]
#[ORM\DiscriminatorColumn(name: 'discr', type: 'string')]
#[
    ORM\DiscriminatorMap([
        NoCapAssignment::DISCRIMINATOR => NoCapAssignment::class,
        IndividualCapAssignment::DISCRIMINATOR => IndividualCapAssignment::class,
        ChannelCapAssignment::DISCRIMINATOR => ChannelCapAssignment::class,
    ])
]
abstract readonly class AbstractCapAssignment
{
    #[ORM\Id]
    #[ORM\Column(type: 'ulid')]
    protected Ulid $id;

    #[ORM\Embedded]
    protected Slot $slot;

    /**
     * @var Pager needed for doctrine relationship mapping
     */
    #[ORM\ManyToOne(targetEntity: Pager::class, inversedBy: 'slots')]
    #[ORM\JoinColumn(name: 'pager_id', referencedColumnName: 'id')]
    private Pager $pager; // @phpstan-ignore doctrine.associationType (field required by ORM)

    public function __construct(Pager $pager, Slot $slot)
    {
        $this->slot = $slot;
        $this->pager = $pager;
        $this->id = Ulid::fromString(Ulid::generate());
    }

    public function getId(): Ulid
    {
        return $this->id;
    }

    public function getSlot(): Slot
    {
        return $this->slot;
    }
}
