<?php

declare(strict_types=1);

namespace App\Core\SendMessage\Application\MessageAddressing;

use App\Core\SendMessage\Model\MessageAddressing\AddressingError;
use App\Core\SendMessage\Model\MessageAddressing\SelectedTransport;

/**
 * Result of evaluating transport configurations for a recipient.
 */
readonly class TransportConfigurationEvaluationResult
{
    /**
     * @param list<SelectedTransport> $selectedTransports
     * @param list<AddressingError>   $errors
     */
    public function __construct(
        public array $selectedTransports,
        public array $errors,
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

    /**
     * Check if any selected transport has continueInHierarchy set to false.
     * Used by group evaluation to determine whether to expand members.
     */
    public function shouldStopHierarchyExpansion(): bool
    {
        return array_any($this->selectedTransports, fn ($selectedTransport): bool => false === $selectedTransport->configuration->getContinueInHierarchy());
    }
}
