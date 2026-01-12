<?php

declare(strict_types=1);

namespace App\View\Web\Admin\TransportManagement\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class TransportConfigurationRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 80)]
    #[Assert\Regex(pattern: '/^[a-z0-9_-]+$/', message: 'Key must only contain lowercase letters, numbers, hyphens and underscores')]
    public string $key = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 80)]
    public string $title = '';

    #[Assert\NotBlank]
    public string $transport = '';

    public bool $enabled = false;

    #[Assert\Json(message: 'Vendor configuration must be valid JSON')]
    public ?string $vendorSpecificConfig = null;
}
