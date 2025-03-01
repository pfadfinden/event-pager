<?php

declare(strict_types=1);

namespace App\Core\MessageRecipient\Handler;

use App\Core\Contracts\Bus\Bus;
use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\MessageRecipient\Command\ReplaceName;
use App\Core\MessageRecipient\Port\MessageRecipientRepository;
use InvalidArgumentException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: Bus::COMMAND)]
final readonly class ReplaceNameHandler
{
    public function __construct(
        private MessageRecipientRepository $repository,
        private UnitOfWork $uow,
    ) {
    }

    public function __invoke(ReplaceName $replaceName): void
    {
        $recipient = $this->repository->getRecipientFromID($replaceName->getRecipientID());
        if (null === $recipient) {
            throw new InvalidArgumentException("Recipient with ID {$replaceName->recipientID} not found.");
        }
        $recipient->setName($replaceName->name);

        $this->uow->commit();
    }
}
