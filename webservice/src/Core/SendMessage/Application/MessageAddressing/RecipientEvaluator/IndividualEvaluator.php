<?php

declare(strict_types=1);

namespace App\Core\SendMessage\Application\MessageAddressing\RecipientEvaluator;

use App\Core\MessageRecipient\Model\Person;
use App\Core\SendMessage\Application\MessageAddressing\TransportConfigurationEvaluator;
use App\Core\SendMessage\Model\MessageAddressing\AddressingResult;
use App\Core\SendMessage\Model\MessageAddressing\EvaluationContext;
use App\Core\TransportContract\Model\Message;

/**
 * Evaluates transport configurations for an individual (Person).
 */
readonly class IndividualEvaluator
{
    public function __construct(
        private TransportConfigurationEvaluator $configurationEvaluator,
    ) {
    }

    public function evaluate(Person $person, EvaluationContext $context, Message $message): AddressingResult
    {
        $result = $this->configurationEvaluator->evaluate(
            $person,
            $context,
            $message,
        );

        return new AddressingResult(
            $person,
            $result->selectedTransports,
            $result->errors,
            [],
        );
    }
}
