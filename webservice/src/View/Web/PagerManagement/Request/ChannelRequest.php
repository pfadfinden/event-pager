<?php

declare(strict_types=1);

namespace App\View\Web\PagerManagement\Request;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\NoSuspiciousCharacters;

final class ChannelRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[NoSuspiciousCharacters(restrictionLevel: NoSuspiciousCharacters::RESTRICTION_LEVEL_ASCII)]
    public string $name;

    #[Assert\NotBlank]
    #[Assert\Range(min: 1, max: 9999)]
    public int $capCode;

    public bool $audible = false;

    public bool $vibration = false;
}
