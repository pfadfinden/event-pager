<?php

declare(strict_types=1);

namespace App\Core\MessageRecipient\Handler;

use App\Core\Contracts\Bus\Bus;
use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\MessageRecipient\Command\BindRecipientToGroup;
use App\Core\MessageRecipient\Model\AbstractMessageRecipient;
use App\Core\MessageRecipient\Model\Group;
use App\Core\MessageRecipient\Port\MessageRecipientRepository;
use InvalidArgumentException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: Bus::COMMAND)]
final readonly class BindRecipientToGroupHandler
{
    public function __construct(
        private MessageRecipientRepository $repository,
        private UnitOfWork $uow,
    ) {
    }

    public function __invoke(BindRecipientToGroup $bindRecipientToGroup): void
    {
        $group = $this->repository->getRecipientFromID($bindRecipientToGroup->getGroupID());
        if (!$group instanceof Group) {
            throw new InvalidArgumentException("Group with ID {$bindRecipientToGroup->getGroupID()} not found.");
        }
        $recipient = $this->repository->getRecipientFromID($bindRecipientToGroup->getRecipientID());
        if (!$recipient instanceof AbstractMessageRecipient) {
            throw new InvalidArgumentException("Recipient with ID {$bindRecipientToGroup->recipientID} not found.");
        }
        $group->addMember($recipient);
        $this->uow->commit();
    }
}
