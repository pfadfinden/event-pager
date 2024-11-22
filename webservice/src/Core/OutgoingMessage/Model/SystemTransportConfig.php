<?php

declare(strict_types=1);

namespace App\Core\OutgoingMessage\Model;

use Doctrine\ORM\Mapping as ORM;
use SensitiveParameter;

/**
 * This entity enables administrators to manage transport
 * through the application instead of through configuration
 * files to improve easy of administration.
 *
 * A SystemTransportConfig combines a technical transport
 * implementation with a user readable title and deployment
 * specific configuration (e.g. API Keys).
 * There can be multiple configurations for one transport.
 */
// #[ORM\Entity]
class SystemTransportConfig
{
    // id

    /**
     * human-readable, unique id to reference within configuration.
     */
    // #[ORM\Column]
    public readonly string $key;

    /**
     * FQCN of OutgoingMessage implementation.
     */
    // #[ORM\Column]
    private readonly string $transport;

    /**
     * human readable identifier.
     */
    // #[ORM\Column(length: 80)]
    private string $title;

    /**
     * Do not allow sending new messages to this transport.
     *
     * Already queued outgoing messages may still be sent.
     */
    // #[ORM\Column]
    private bool $enabled = false;

    /**
     * What ever data needs to be available to the transport centrally (e.g. API Keys).
     */
    // #[ORM\Column]
    private ?array $vendorSpecificConfig; // @phpstan-ignore missingType.iterableValue (JSON compatible array)

    public function __construct(string $key, string $transport)
    {
        $this->key = $key;
        $this->transport = $transport;
    }

    public function transport(): string
    {
        return $this->transport;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @returns ?array vendor-specific json-compatible configuration data, e.g. API Keys
     */
    public function getVendorSpecificConfig(): ?array // @phpstan-ignore missingType.iterableValue (JSON compatible array)
    {
        return $this->vendorSpecificConfig;
    }

    /**
     * Add configuration details specific to the transport vendor (e.g. API Keys).
     *
     * @phpstan-ignore-next-line missingType.iterableValue (JSON compatible array)
     */
    public function setVendorSpecificConfig(
        #[SensitiveParameter]
        ?array $vendorSpecificConfig,
    ): void {
        $this->vendorSpecificConfig = $vendorSpecificConfig;
    }
}
