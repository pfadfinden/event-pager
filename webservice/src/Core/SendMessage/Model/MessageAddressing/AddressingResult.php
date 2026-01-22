<?php

declare(strict_types=1);

namespace App\Core\SendMessage\Model\MessageAddressing;

use App\Core\MessageRecipient\Model\MessageRecipient;

/**
 * Result of evaluating transport configurations for a single recipient.
 */
readonly class AddressingResult
{
    /**
     * @param list<SelectedTransport> $selectedTransports
     * @param list<AddressingError>   $errors
     * @param list<MessageRecipient>  $membersToExpand
     */
    public function __construct(
        public MessageRecipient $recipient,
        public array $selectedTransports,
        public array $errors,
        public array $membersToExpand = [],
    ) {
    }

    public function hasSelectedTransports(): bool
    {
        return [] !== $this->selectedTransports;
    }

    public function hasErrors(): bool
    {
        return [] !== $this->errors;
    }

    public function hasMembersToExpand(): bool
    {
        return [] !== $this->membersToExpand;
    }
}
