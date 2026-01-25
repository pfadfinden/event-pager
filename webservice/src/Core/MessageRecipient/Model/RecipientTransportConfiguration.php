<?php

declare(strict_types=1);

namespace App\Core\MessageRecipient\Model;

use Doctrine\DBAL\Types\Types;
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
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $vendorSpecificConfig = null; // @phpstan-ignore missingType.iterableValue (JSON compatible array)

    /**
     * Higher rank = evaluated first. Transport configs are evaluated in descending rank order.
     */
    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    private int $rank = 0;

    /**
     * Symfony Expression Language expression that must evaluate to true for this config to be selected.
     * Available variables: priority, priorityValue, currentTime, hour, dayOfWeek, contentLength.
     */
    #[ORM\Column(type: Types::STRING, length: 500, options: ['default' => 'true'])]
    private string $selectionExpression = 'true';

    /**
     * For groups: whether to expand members after this config matches.
     * NULL = not applicable (for individuals/roles), true = continue expansion, false = stop expansion.
     */
    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $continueInHierarchy = null;

    /**
     * Whether to continue evaluating other transport configurations after this one matches.
     * If false, stops evaluating further configs when this one is selected.
     */
    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    private bool $evaluateOtherTransportConfigurations = true;

    public function __construct(AbstractMessageRecipient $recipient, string $key, ?Ulid $id = null)
    {
        $this->id = $id ?? new Ulid();
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

    public function getRank(): int
    {
        return $this->rank;
    }

    public function setRank(int $rank): void
    {
        $this->rank = $rank;
    }

    public function getSelectionExpression(): string
    {
        return $this->selectionExpression;
    }

    public function setSelectionExpression(string $selectionExpression): void
    {
        $this->selectionExpression = $selectionExpression;
    }

    public function getContinueInHierarchy(): ?bool
    {
        return $this->continueInHierarchy;
    }

    public function setContinueInHierarchy(?bool $continueInHierarchy): void
    {
        $this->continueInHierarchy = $continueInHierarchy;
    }

    public function shouldEvaluateOtherTransportConfigurations(): bool
    {
        return $this->evaluateOtherTransportConfigurations;
    }

    public function setEvaluateOtherTransportConfigurations(bool $evaluateOtherTransportConfigurations): void
    {
        $this->evaluateOtherTransportConfigurations = $evaluateOtherTransportConfigurations;
    }
}
