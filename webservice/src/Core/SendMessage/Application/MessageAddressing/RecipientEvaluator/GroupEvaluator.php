<?php

declare(strict_types=1);

namespace App\Core\SendMessage\Application\MessageAddressing\RecipientEvaluator;

use App\Core\MessageRecipient\Model\Group;
use App\Core\SendMessage\Application\MessageAddressing\TransportConfigurationEvaluator;
use App\Core\SendMessage\Model\MessageAddressing\AddressingError;
use App\Core\SendMessage\Model\MessageAddressing\AddressingResult;
use App\Core\SendMessage\Model\MessageAddressing\EvaluationContext;
use App\Core\TransportContract\Model\Message;

/**
 * Evaluates transport configurations for a group.
 *
 * Groups can send to their own transport configurations and optionally expand to members.
 */
readonly class GroupEvaluator
{
    public function __construct(
        private TransportConfigurationEvaluator $configurationEvaluator,
    ) {
    }

    public function evaluate(Group $group, EvaluationContext $context, Message $message): AddressingResult
    {
        if (!$group->hasTransportConfigurations() && !$group->canResolve()) {
            return new AddressingResult(
                $group,
                [],
                [AddressingError::emptyGroupWithNoConfigurations($group)],
                [],
            );
        }

        if (!$group->hasTransportConfigurations()) {
            // Since there are no configurations, expand to members
            return new AddressingResult(
                $group,
                [],
                [],
                $group->resolve(),
            );
        }

        $result = $this->configurationEvaluator->evaluate(
            $group,
            $context,
            $message,
        );

        return new AddressingResult(
            $group,
            $result->selectedTransports,
            $result->errors,
            $group->canResolve() && (!$result->hasSelectedTransports() || !$result->shouldStopHierarchyExpansion()) ? $group->resolve() : []
        );
    }
}
