<?php

declare(strict_types=1);

namespace App\View\Web\SendMessage;

use Symfony\Component\Validator\Constraints as Assert;
use function is_array;
use function is_string;

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

        if (!is_array($decoded)) {
            return [];
        }

        return array_values(array_filter($decoded, fn (mixed $transport) => is_string($transport)));
    }
}
