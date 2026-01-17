<?php

declare(strict_types=1);

namespace App\View\Web\PagerManagement\Request;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\NoSuspiciousCharacters;
use const PHP_INT_MAX;

final class PagerRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[NoSuspiciousCharacters(restrictionLevel: NoSuspiciousCharacters::RESTRICTION_LEVEL_ASCII)]
    public string $label;

    #[Assert\NotBlank]
    #[Assert\Range(min: 0, max: PHP_INT_MAX)]
    public int $number;

    #[NoSuspiciousCharacters(restrictionLevel: NoSuspiciousCharacters::RESTRICTION_LEVEL_ASCII)]
    public ?string $comment = null;
}
