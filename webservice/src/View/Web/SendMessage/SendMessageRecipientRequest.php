<?php

declare(strict_types=1);

namespace App\View\Web\SendMessage;

use Symfony\Component\Validator\Constraints as Assert;

final class SendMessageRecipientRequest
{
    #[Assert\NotBlank()]
    #[Assert\Ulid()]
    public string $id;

    public string $label;

    #[Assert\Choice(['GROUP', 'ROLE', 'PERSON'])]
    public ?string $type = null;

    /**
     * JSON-encoded array of enabled transport class names for this recipient.
     */
    public ?string $enabledTransports = null;

    /**
     * @return list<string>
     */
    public function getEnabledTransportsArray(): array
    {
        if (null === $this->enabledTransports || '' === $this->enabledTransports) {
            return [];
        }

        $decoded = json_decode($this->enabledTransports, true);

        return is_array($decoded) ? $decoded : [];
    }
}
