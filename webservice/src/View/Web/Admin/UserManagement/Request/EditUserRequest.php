<?php

declare(strict_types=1);

namespace App\View\Web\Admin\UserManagement\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class EditUserRequest
{
    #[Assert\Length(max: 180)]
    public ?string $displayname = null;

    #[Assert\Length(min: 8, max: 255)]
    public ?string $password = null;

    /**
     * @var string[]
     */
    public array $roles = [];
}
