<?php

declare(strict_types=1);

namespace App\Core\MessageRecipient\Handler;

use App\Core\Contracts\Bus\Bus;
use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\MessageRecipient\Command\RemoveRecipientFromGroup;
use App\Core\MessageRecipient\Model\AbstractMessageRecipient;
use App\Core\MessageRecipient\Model\Group;
use App\Core\MessageRecipient\Port\MessageRecipientRepository;
use InvalidArgumentException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: Bus::COMMAND)]
final readonly class RemoveRecipientFromGroupHandler
{
    public function __construct(
        private MessageRecipientRepository $repository,
        private UnitOfWork $uow,
    ) {
    }

    public function __invoke(RemoveRecipientFromGroup $command): void
    {
        $group = $this->repository->getRecipientFromID($command->getGroupID());
        if (!$group instanceof Group) {
            throw new InvalidArgumentException("Group with ID {$command->getGroupID()} not found.");
        }
        $recipient = $this->repository->getRecipientFromID($command->getRecipientID());
        if (!$recipient instanceof AbstractMessageRecipient) {
            throw new InvalidArgumentException("Recipient with ID {$command->recipientID} not found.");
        }
        $group->removeMember($recipient);
        $this->uow->commit();
    }
}
