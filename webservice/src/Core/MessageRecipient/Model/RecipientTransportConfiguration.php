<?php

declare(strict_types=1);

namespace App\Core\MessageRecipient\Model;

use Doctrine\ORM\Mapping as ORM;
use SensitiveParameter;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity]
class RecipientTransportConfiguration
{
    #[ORM\Column(type: UlidType::NAME)]
    #[ORM\Id]
    private readonly Ulid $id;

    #[ORM\ManyToOne(AbstractMessageRecipient::class, inversedBy: 'transportConfiguration')]
    // @phpstan-ignore doctrine.associationType (only mapping, no access)
    private readonly AbstractMessageRecipient $recipient;

    /**
     * identifier of this transport (human-readable slug).
     */
    #[ORM\Column(length: 80)]
    private readonly string $key;

    /**
     * Quickly disable and enable without removing and re-adding the config.
     */
    #[ORM\Column]
    public bool $isEnabled = true;

    /**
     * What ever data needs to be available to the transport centrally (e.g. API Keys).
     */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $vendorSpecificConfig = null; // @phpstan-ignore missingType.iterableValue (JSON compatible array)

    public function __construct(AbstractMessageRecipient $recipient, string $key)
    {
        $this->id = Ulid::fromString(Ulid::generate());
        $this->recipient = $recipient;
        $this->key = $key;
    }

    public function getId(): Ulid
    {
        return $this->id;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @returns ?array vendor-specific json-compatible configuration data, e.g. API Keys
     */
    public function getVendorSpecificConfig(): ?array // @phpstan-ignore missingType.iterableValue (JSON compatible array)
    {
        return $this->vendorSpecificConfig ?? [];
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
