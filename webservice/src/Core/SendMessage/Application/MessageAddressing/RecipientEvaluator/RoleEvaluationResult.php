<?php

declare(strict_types=1);

namespace App\Core\SendMessage\Application\MessageAddressing\RecipientEvaluator;

use App\Core\MessageRecipient\Model\Person;
use App\Core\SendMessage\Model\MessageAddressing\AddressingResult;

/**
 * Result of evaluating a role.
 *
 * Either the role was evaluated directly, or it delegates to an assigned individual.
 */
readonly class RoleEvaluationResult
{
    private function __construct(
        public bool $isDelegatedToIndividual,
        public ?Person $delegatedIndividual,
        public ?AddressingResult $addressingResult,
    ) {
    }

    public static function delegatedToIndividual(Person $person): self
    {
        return new self(true, $person, null);
    }

    public static function evaluated(AddressingResult $result): self
    {
        return new self(false, null, $result);
    }
}
