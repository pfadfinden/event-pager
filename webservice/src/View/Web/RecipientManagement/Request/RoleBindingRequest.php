<?php

declare(strict_types=1);

namespace App\View\Web\RecipientManagement\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class RoleBindingRequest
{
    #[Assert\NotBlank]
    public string $personId = '';
}
