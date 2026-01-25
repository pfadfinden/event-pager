<?php

declare(strict_types=1);

namespace App\Core\MessageRecipient\Command;

use InvalidArgumentException;
use Symfony\Component\Uid\Ulid;

final readonly class UpdateTransportConfiguration
{
    /**
     * @param array<mixed>|null $vendorSpecificConfig
     */
    public function __construct(
        public string $recipientId,
        public string $configId,
        public ?array $vendorSpecificConfig = null,
        public bool $isEnabled = true,
        public ?int $rank = null,
        public string $selectionExpression = 'true',
        public ?bool $continueInHierarchy = null,
        public bool $evaluateOtherTransportConfigurations = true,
    ) {
    }

    public function getRecipientId(): Ulid
    {
        if (!Ulid::isValid($this->recipientId)) {
            throw new InvalidArgumentException('Malformed recipient ID');
        }

        return Ulid::fromString($this->recipientId);
    }

    public function getConfigId(): Ulid
    {
        if (!Ulid::isValid($this->configId)) {
            throw new InvalidArgumentException('Malformed config ID');
        }

        return Ulid::fromString($this->configId);
    }
}
