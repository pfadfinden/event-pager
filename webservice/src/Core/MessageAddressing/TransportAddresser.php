<?php

declare(strict_types=1);

namespace App\Core\MessageAddressing;

use App\Core\MessageRecipient\Model\Delegated;
use App\Core\MessageRecipient\Model\Group;
use App\Core\MessageRecipient\Model\MessageRecipient;
use App\Core\MessageRecipient\Model\Role;
use Exception;
use Iterator;
use SplObjectStorage;

class TransportAddresser
{
    public function __construct(
        public readonly Transport $transport
    ) {
    }

    /**
     * @param list<MessageRecipient> $recipients
     *
     * @return Iterator<MessageRecipient, SendStatus>
     */
    public function sendTo(array $recipients, Priority $priority, string $message): Iterator
    {
        [$reachable, $unreachable] = $this->resolve($recipients, $priority);

        foreach ($reachable as $recipient) {
            try {
                $this->transport->sendTo($recipient, $priority, $message);

                yield $recipient => SendSuccess::SENT;
            } catch (Exception) {
                yield $recipient => SendSuccess::ERROR;
            }
        }

        foreach ($unreachable as $recipient) {
            yield $recipient => SendSuccess::NOT_SENT;
        }
    }

    /**
     * @param list<MessageRecipient> $recipients
     *
     * @return array{0: list<Recipient>, 1: list<Recipient>}
     */
    private function resolve(array $recipients, Priority $priority): array
    {
        $queue = new SplObjectStorage();
        foreach ($recipients as $recipient) {
            $queue->attach($recipient);
        }

        $remove = new SplObjectStorage();
        $unreachable = new SplObjectStorage();

        foreach ($queue as $recipient) {
            if ($this->transport->canSendTo($recipient, $priority)) {
                if ($recipient instanceof Group) {
                    foreach ($recipient->getMembersRecursively() as $member) {
                        $remove->attach($member);
                    }
                } elseif ($recipient instanceof Role) {
                    if (null !== $recipient->person) {
                        $remove->attach($recipient->person);
                    }
                }
            } elseif ($recipient instanceof Delegated) {
                if ($recipient->canResolve()) {
                    foreach ($recipient->resolve() as $member) {
                        $queue->attach($member);
                    }
                    $remove->attach($recipient);
                } else {
                    $unreachable->attach($recipient);
                }
            } else {
                $unreachable->attach($recipient);
            }
        }

        $queue->removeAll($remove);

        return [
            iterator_to_array($queue, false),
            iterator_to_array($unreachable, false)
        ];
    }
}
