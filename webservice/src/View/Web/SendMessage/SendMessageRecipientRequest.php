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
}
