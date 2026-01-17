<?php

declare(strict_types=1);

namespace App\View\Web\Admin\UserManagement\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class AddUserRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 180)]
    public string $username = '';

    #[Assert\Length(max: 180)]
    public ?string $displayname = null;

    #[Assert\NotBlank]
    #[Assert\Length(min: 8, max: 255)]
    public string $password = '';
}
