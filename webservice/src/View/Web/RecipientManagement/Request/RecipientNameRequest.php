<?php

declare(strict_types=1);

namespace App\View\Web\RecipientManagement\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class RecipientNameRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $name = '';
}
