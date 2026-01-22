<?php

declare(strict_types=1);

namespace App\Core\SendMessage\Model\MessageAddressing;

use App\Core\MessageRecipient\Model\MessageRecipient;
use App\Core\MessageRecipient\Model\RecipientTransportConfiguration;

/**
 * Represents an error that occurred during message addressing.
 */
readonly class AddressingError
{
    private function __construct(
        public AddressingErrorType $type,
        public MessageRecipient $recipient,
        public ?RecipientTransportConfiguration $configuration,
        public ?string $details,
    ) {
    }

    public static function noTransportConfigurations(MessageRecipient $recipient): self
    {
        return new self(
            AddressingErrorType::NO_TRANSPORT_CONFIGURATIONS,
            $recipient,
            null,
            null,
        );
    }

    public static function noMatchingConfigurations(MessageRecipient $recipient): self
    {
        return new self(
            AddressingErrorType::NO_MATCHING_CONFIGURATIONS,
            $recipient,
            null,
            null,
        );
    }

    public static function expressionEvaluationFailed(
        MessageRecipient $recipient,
        RecipientTransportConfiguration $configuration,
        string $details,
    ): self {
        return new self(
            AddressingErrorType::EXPRESSION_EVALUATION_FAILED,
            $recipient,
            $configuration,
            $details,
        );
    }

    public static function emptyGroupWithNoConfigurations(MessageRecipient $recipient): self
    {
        return new self(
            AddressingErrorType::EMPTY_GROUP_NO_CONFIGURATIONS,
            $recipient,
            null,
            null,
        );
    }

    public static function transportNotFound(
        MessageRecipient $recipient,
        RecipientTransportConfiguration $configuration,
    ): self {
        return new self(
            AddressingErrorType::TRANSPORT_NOT_FOUND,
            $recipient,
            $configuration,
            null,
        );
    }
}
