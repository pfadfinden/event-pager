<?php

declare(strict_types=1);

namespace App\Core\IntelPage\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Uid\Ulid;
use function strlen;

#[ORM\Entity]
class Pager
{
    public const int PAGER_SLOT_MIN = 0;
    public const int PAGER_SLOT_MAX = 7;

    #[ORM\Id]
    #[ORM\Column(type: 'ulid')]
    private readonly Ulid $id;

    /**
     * @var string human identifier describing pager use
     */
    #[ORM\Column(length: 255)]
    private string $label;

    /**
     * @var int human identifier as printed on the hardware
     */
    #[ORM\Column]
    private int $number;

    /**
     * List of cap code assignments.
     *
     * @var Collection<int, AbstractCapAssignment>
     */
    #[ORM\OneToMany(
        targetEntity: AbstractCapAssignment::class,
        mappedBy: 'slots',
        cascade: ['persist', 'remove'],
        orphanRemoval: true,
        indexBy: 'slot'
    )]
    private Collection $slots;

    /**
     * @param string                  $label  see property description
     * @param int                     $number see property description
     * @param AbstractCapAssignment[] $slots  list of assignments
     */
    public function __construct(
        Ulid $id,
        string $label,
        int $number,
        array $slots = [],
    ) {
        $this->id = $id;
        $this->slots = new ArrayCollection($slots);
        $this->label = $label;
        $this->number = $number;
    }

    public function getId(): Ulid
    {
        return $this->id;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    private function isInSlotBounds(Slot $slot): bool
    {
        return ($slot->getSlot() >= self::PAGER_SLOT_MIN) && ($slot->getSlot() <= self::PAGER_SLOT_MAX);
    }

    public function getCapAssignment(Slot $atSlot): ?AbstractCapAssignment
    {
        if (!$this->isInSlotBounds($atSlot)) {
            throw new InvalidArgumentException('Trying to access out of bounds slot!');
        }

        return $this->slots->get($atSlot->getSlot());
    }

    /**
     * @return AbstractCapAssignment[]
     */
    public function getCapAssignments(): iterable
    {
        return $this->slots->toArray();
    }

    public function assignIndividualCap(Slot $slot,
        CapCode $capCode,
        bool $audible,
        bool $vibration): static
    {
        $assignment = new IndividualCapAssignment($this, $slot, $capCode, $audible, $vibration);

        // NOTE: Should probably inform the caller, that, if the slot is already taken,
        //       the previous assignment is now overwritten (or provide an option not
        //       to do that)
        $this->slots->set($assignment->getSlot()->getSlot(), $assignment);

        return $this;
    }

    public function assignChannel(Slot $slot, Channel $channel): static
    {
        $assignment = new ChannelCapAssignment($this, $slot, $channel);

        // NOTE: Should probably inform the caller, that, if the slot is already taken,
        //       the previous assignment is now overwritten (or provide an option not
        //       to do that)
        $this->slots->set($assignment->getSlot()->getSlot(), $assignment);

        return $this;
    }

    public function clearSlot(Slot $slot): static
    {
        // NOTE: Should probably inform the caller, that, if the slot is already taken,
        //       the previous assignment is now overwritten (or provide an option not
        //       to do that)
        $this->slots->remove($slot->getSlot());

        return $this;
    }

    public function setLabel(string $label): static
    {
        if (strlen($label) > 255 || '' === $label) {
            throw new InvalidArgumentException('The length of the new label must be from 0 to 255 characters!');
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
