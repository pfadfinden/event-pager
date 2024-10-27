<?php

namespace App\View\Web\SendMessage;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\NoSuspiciousCharacters;

final class SendMessageRequest
{
    /**
     * The text body to send (ASCII only).
     */
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[NoSuspiciousCharacters(restrictionLevel: NoSuspiciousCharacters::RESTRICTION_LEVEL_ASCII)]
    public string $message;

    /**
     * Numeric indicator of message urgency.
     *
     * 1 - low to 5 - high
     */
    #[Assert\NotBlank]
    #[Assert\Range(min: 1, max: 5)]
    public int $priority = 3;

    /**
     * The text body to send.
     */
    #[Assert\NotBlank]
    #[Assert\Count(min: 1)]
    #[Assert\All([
        new Assert\NotBlank(),
        new Assert\Ulid(),
    ])]
    public array $to;
}
