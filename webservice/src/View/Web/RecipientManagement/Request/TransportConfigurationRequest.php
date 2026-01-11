<?php

declare(strict_types=1);

namespace App\View\Web\RecipientManagement\Request;

use Symfony\Component\Validator\Constraints as Assert;
use function is_array;

class TransportConfigurationRequest
{
    #[Assert\NotBlank]
    public ?string $transportKey = null;

    public bool $isEnabled = true;

    /**
     * JSON string that will be decoded to array.
     */
    public ?string $vendorSpecificConfig = null;

    /**
     * @return array<mixed>|null
     */
    public function getVendorSpecificConfigArray(): ?array
    {
        if (null === $this->vendorSpecificConfig || '' === trim($this->vendorSpecificConfig)) {
            return null;
        }

        $decoded = json_decode($this->vendorSpecificConfig, true);

        return is_array($decoded) ? $decoded : null;
    }
}
