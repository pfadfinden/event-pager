<?php

declare(strict_types=1);

namespace App\Core\TransportManager\Model;

use App\Core\TransportContract\Model\SystemTransportConfig;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use SensitiveParameter;
use function assert;
use function strlen;

#[ORM\Entity]
class TransportConfiguration implements SystemTransportConfig
{
    /**
     * identifier of this transport (human-readable slug).
     */
    #[ORM\Column(length: 80)]
    #[ORM\Id]
    private readonly string $key;

    /**
     * FQCN of Transport implementation.
     *
     * @see getTransport
     *
     * @var class-string
     */
    #[ORM\Column]
    private string $transport; // @phpstan-ignore doctrine.columnType (database does not know class-string)

    /**
     * human readable identifier.
     */
    #[ORM\Column(length: 80)]
    private string $title;

    /**
     * Do not allow sending new messages to this transport.
     *
     * Already queued outgoing messages may still be sent.
     */
    #[ORM\Column]
    private bool $enabled = false;

    /**
     * What ever data needs to be available to the transport centrally (e.g. API Keys).
     */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $vendorSpecificConfig = null; // @phpstan-ignore missingType.iterableValue (JSON compatible array)

    /**
     * @param class-string $transport
     */
    public function __construct(string $key, string $transport, string $title)
    {
        assert(strlen($key) <= 80);
        assert(strlen($title) <= 80);

        $this->key = $key;
        $this->transport = $transport;
        $this->title = $title;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return class-string
     */
    public function getTransport(): string
    {
        return $this->transport;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setTitle(string $title): void
    {
        assert(strlen($title) <= 80);
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

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    /**
     * @param class-string $transport
     */
    public function setTransport(string $transport): void
    {
        $this->transport = $transport;
    }
}
