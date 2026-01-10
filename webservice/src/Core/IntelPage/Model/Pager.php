<?php

declare(strict_types=1);

namespace App\Core\IntelPage\Model;

use App\Core\MessageRecipient\Model\AbstractMessageRecipient;
use App\Core\MessageRecipient\Model\MessageRecipient;
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
     * @var string human identifier describing pager use
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $comment;

    /**
     * @var bool shows whether the pager is activated or not
     *
     * Deactivated pagers will be skipped when sending messages
     */
    #[ORM\Column]
    private bool $activated = false;

    /**
     * List of cap code assignments.
     *
     * @var Collection<int, AbstractCapAssignment>
     */
    #[ORM\OneToMany(
        targetEntity: AbstractCapAssignment::class,
        mappedBy: 'pager',
        cascade: ['persist', 'remove'],
        orphanRemoval: true,
        indexBy: 'slot.slot'
    )]
    private Collection $slots;

    #[ORM\ManyToOne(
        targetEntity: AbstractMessageRecipient::class,
        fetch: 'LAZY',
    )]
    private ?AbstractMessageRecipient $carriedBy = null;

    /**
     * @param string $label  see property description
     * @param int    $number see property description
     */
    public function __construct(
        Ulid $id,
        string $label,
        int $number,
    ) {
        $this->id = $id;
        $this->slots = new ArrayCollection();
        $this->setLabel($label);
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

    public function getCapAssignment(Slot $atSlot): ?AbstractCapAssignment
    {
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
        $element = $this->getCapAssignment($slot);

        if ($element instanceof AbstractCapAssignment) {
            $this->slots->removeElement($element);
        }

        return $this;
    }

    public function setLabel(string $label): static
    {
        if (strlen($label) > 255 || '' === $label) {
            throw new InvalidArgumentException('The length of the new label must be from 1 to 255 characters!');
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

    public function getComment(): string
    {
        return $this->comment ?? '';
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    public function isActivated(): bool
    {
        return $this->activated;
    }

    public function setActivated(bool $activated): static
    {
        $this->activated = $activated;

        return $this;
    }

    public function individualAlertCap(): ?CapCode
    {
        foreach ($this->slots as $slot) {
            if (!$slot instanceof IndividualCapAssignment) {
                continue;
            }

            if ($slot->isAudible()) {
                return $slot->getCapCode();
            }
        }

        return null;
    }

    public function individualNonAlertCap(): ?CapCode
    {
        foreach ($this->slots as $slot) {
            if (!$slot instanceof IndividualCapAssignment) {
                continue;
            }

            if (!$slot->isAudible()) {
                return $slot->getCapCode();
            }
        }

        return null;
    }

    public function getCarriedBy(): ?MessageRecipient
    {
        return $this->carriedBy;
    }

    public function setCarriedBy(?AbstractMessageRecipient $carriedBy): self
    {
        $this->carriedBy = $carriedBy;

        return $this;
    }
}
