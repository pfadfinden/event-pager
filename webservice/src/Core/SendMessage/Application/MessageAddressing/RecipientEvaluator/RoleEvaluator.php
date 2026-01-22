<?php

declare(strict_types=1);

namespace App\Core\SendMessage\Application\MessageAddressing\RecipientEvaluator;

use App\Core\MessageRecipient\Model\Person;
use App\Core\MessageRecipient\Model\Role;
use App\Core\SendMessage\Application\MessageAddressing\TransportConfigurationEvaluator;
use App\Core\SendMessage\Model\MessageAddressing\AddressingResult;
use App\Core\SendMessage\Model\MessageAddressing\EvaluationContext;
use App\Core\TransportContract\Model\Message;

/**
 * Evaluates transport configurations for a role.
 *
 * If the role has an assigned individual, delegates to that individual.
 * Otherwise, evaluates the role's own transport configurations.
 */
readonly class RoleEvaluator
{
    public function __construct(
        private TransportConfigurationEvaluator $configurationEvaluator,
    ) {
    }

    public function evaluate(Role $role, EvaluationContext $context, Message $message): RoleEvaluationResult
    {
        if ($role->person instanceof Person) {
            return RoleEvaluationResult::delegatedToIndividual($role->person);
        }

        // if role was not assigned, check its direct transport configurations
        $result = $this->configurationEvaluator->evaluate(
            $role,
            $context,
            $message,
        );

        return RoleEvaluationResult::evaluated(new AddressingResult(
            $role,
            $result->selectedTransports,
            $result->errors,
            [],
        ));
    }
}
