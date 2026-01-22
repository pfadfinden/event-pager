<?php

declare(strict_types=1);

namespace App\Core\SendMessage\Application\MessageAddressing;

use App\Core\MessageRecipient\Model\Group;
use App\Core\MessageRecipient\Model\MessageRecipient;
use App\Core\MessageRecipient\Model\Person;
use App\Core\MessageRecipient\Model\Role;
use App\Core\SendMessage\Application\MessageAddressing\RecipientEvaluator\GroupEvaluator;
use App\Core\SendMessage\Application\MessageAddressing\RecipientEvaluator\IndividualEvaluator;
use App\Core\SendMessage\Application\MessageAddressing\RecipientEvaluator\RoleEvaluator;
use App\Core\SendMessage\Model\MessageAddressing\AddressingResult;
use App\Core\SendMessage\Model\MessageAddressing\EvaluationContext;
use App\Core\SendMessage\Port\Clock;
use App\Core\TransportContract\Model\Message;
use LogicException;
use SplQueue;
use function assert;
use function strlen;

/**
 * Main entry point for resolving recipients to transport configurations.
 *
 * Uses a queue-based algorithm with deduplication to handle nested groups,
 * cycles, and role delegation.
 */
readonly class RecipientResolver
{
    public function __construct(
        private IndividualEvaluator $individualEvaluator,
        private RoleEvaluator $roleEvaluator,
        private GroupEvaluator $groupEvaluator,
        private Clock $clock,
    ) {
    }

    /**
     * Resolves a list of recipients to their selected transport configurations.
     *
     * @param iterable<MessageRecipient> $recipients
     *
     * @return list<AddressingResult>
     */
    public function resolve(
        iterable $recipients,
        Message $message,
    ): array {
        $context = $this->deriveEvaluationContext($message);
        $queue = $this->initQueue($recipients);

        /** @var array<string, bool> $evaluated used to not evaluate one recipient twice */
        $evaluated = [];

        /** @var list<AddressingResult> $results */
        $results = [];

        while (!$queue->isEmpty()) {
            $recipient = $queue->dequeue();
            $recipientId = $recipient->getId()->toRfc4122();

            if (isset($evaluated[$recipientId])) {
                continue;
            }

            $evaluated[$recipientId] = true;

            $result = $this->evaluateRecipient($recipient, $context, $message);
            $results[] = $result;

            foreach ($result->membersToExpand as $member) {
                $memberId = $member->getId()->toRfc4122();
                if (!isset($evaluated[$memberId])) {
                    $queue->enqueue($member);
                }
            }
        }

        return $results;
    }

    /**
     * @param iterable<MessageRecipient> $recipients
     *
     * @return SplQueue<MessageRecipient>
     */
    private function initQueue(iterable $recipients): SplQueue
    {
        /** @var SplQueue<MessageRecipient> $queue */
        $queue = new SplQueue();

        foreach ($recipients as $recipient) {
            $queue->enqueue($recipient);
        }

        return $queue;
    }

    private function evaluateRecipient(
        MessageRecipient $recipient,
        EvaluationContext $context,
        Message $message,
    ): AddressingResult {
        return match (true) {
            $recipient instanceof Person => $this->individualEvaluator->evaluate($recipient, $context, $message),
            $recipient instanceof Role => $this->evaluateRole($recipient, $context, $message),
            $recipient instanceof Group => $this->groupEvaluator->evaluate($recipient, $context, $message),
            default => throw new LogicException('Unknown recipient type: '.$recipient::class),
        };
    }

    private function evaluateRole(Role $role, EvaluationContext $context, Message $message): AddressingResult
    {
        $roleResult = $this->roleEvaluator->evaluate($role, $context, $message);

        if ($roleResult->isDelegatedToIndividual && $roleResult->delegatedIndividual instanceof Person) {
            return $this->individualEvaluator->evaluate($roleResult->delegatedIndividual, $context, $message);
        }

        assert($roleResult->addressingResult instanceof AddressingResult);

        return $roleResult->addressingResult;
    }

    private function deriveEvaluationContext(Message $message): EvaluationContext
    {
        return new EvaluationContext(
            $message->priority,
            $this->clock->now(),
            strlen($message->body),
        );
    }
}
