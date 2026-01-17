<?php

declare(strict_types=1);

namespace App\Core\MessageRecipient\Handler;

use App\Core\Contracts\Bus\Bus;
use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\MessageRecipient\Command\DeleteRecipient;
use App\Core\MessageRecipient\Model\AbstractMessageRecipient;
use App\Core\MessageRecipient\Model\Person;
use App\Core\MessageRecipient\Port\MessageRecipientRepository;
use InvalidArgumentException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use function count;

#[AsMessageHandler(bus: Bus::COMMAND)]
final readonly class DeleteRecipientHandler
{
    public function __construct(
        private MessageRecipientRepository $repository,
        private UnitOfWork $uow,
    ) {
    }

    /**
     * This will initiate the removal of a recipient from the database. many-to-many relationships should be handled by doctrine.
     * In case a person to be deleted is still assigned to a role an error will be thrown.
     */
    public function __invoke(DeleteRecipient $recipientToDelete): void
    {
        $recipient = $this->repository->getRecipientFromID($recipientToDelete->getRecipientID());
        if (!$recipient instanceof AbstractMessageRecipient) {
            throw new InvalidArgumentException("Recipient with ID {$recipientToDelete->recipientID} not found.");
        }
        if ($recipient instanceof Person) {
            $assignedRoles = $recipient->getRoles();
            if (count($assignedRoles) > 0) {
                throw new InvalidArgumentException("Person with ID {$recipientToDelete->recipientID} is still assigned to roles: ".implode(', ', $assignedRoles));
            }
        }
        $this->repository->remove($recipient);
        $this->uow->commit();
    }
}
