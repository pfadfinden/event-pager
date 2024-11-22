<?php

declare(strict_types=1);

namespace App\Core\MessageAddressing;

use App\Core\MessageRecipient\Model\Delegated;
use App\Core\MessageRecipient\Model\Group;
use App\Core\MessageRecipient\Model\MessageRecipient;
use App\Core\MessageRecipient\Model\Role;
use Iterator;
use SplObjectStorage;

final class RecipientSuccessMap
{
    /**
     * @var SplObjectStorage<MessageRecipient, SentSuccess>
     */
    private $wrapped;

    public function __construct()
    {
        $this->wrapped = new SplObjectStorage();
    }

    public function addIfMoreSuccessful(MessageRecipient $recipient, SendSuccess $success): void
    {
        if ($recipient instanceof Group) {
            foreach ($recipient->getMembers() as $member) {
                $this->addIfMoreSuccessful($member, $success);
            }
        } elseif ($recipient instanceof Role && null !== $recipient->person) {
            $this->addIfMoreSuccessful($recipient->person, $success);
        }

        if (!isset($this->wrapped[$recipient]) || $success->value > $this->wrapped[$recipient]->value) {
            $this->wrapped[$recipient] = $success;
        }
    }

    /**
     * @return Iterator<MessageRecipient>
     */
    public function getUnreachableRecipients(): Iterator
    {
        $wrapped = clone $this->wrapped;
        foreach ($wrapped as $recipient) {
            if ($recipient instanceof Delegated) {
                continue;
            }

            if ($wrapped[$recipient]->value < SendSuccess::SENT) {
                yield $recipient;
            }
        }
    }
}
