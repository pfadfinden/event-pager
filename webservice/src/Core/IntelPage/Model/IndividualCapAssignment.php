<?php

declare(strict_types=1);

namespace App\Core\IntelPage\Model;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
readonly class IndividualCapAssignment extends AbstractCapAssignment
{
    public const string DISCRIMINATOR = 'in';

    #[ORM\Embedded]
    private CapCode $capCode;

    #[ORM\Column]
    private bool $audible;

    #[ORM\Column]
    private bool $vibration;

    public function __construct(
        Pager $pager,
        Slot $slot,
        CapCode $capCode,
        bool $audible,
        bool $vibration,
    ) {
        parent::__construct($pager, $slot);
        $this->capCode = $capCode;
        $this->audible = $audible;
        $this->vibration = $vibration;
    }

    public function getCapCode(): CapCode
    {
        return $this->capCode;
    }

    public function isAudible(): bool
    {
        return $this->audible;
    }

    public function isVibration(): bool
    {
        return $this->vibration;
    }
}
