<?php

declare(strict_types=1);

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
     * @var SendMessageRecipientRequest[]
     */
    #[Assert\NotBlank]
    #[Assert\Count(min: 1)]
    public array $to;

    /**
     * @return string[]
     */
    public function toIds(): array
    {
        return array_values(array_map(fn (SendMessageRecipientRequest $to) => $to->id, $this->to));
    }
}
