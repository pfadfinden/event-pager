<?php

declare(strict_types=1);

namespace App\Core\MessageRecipient\Handler;

use App\Core\Contracts\Bus\Bus;
use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\MessageRecipient\Command\RemoveTransportConfiguration;
use App\Core\MessageRecipient\Port\MessageRecipientRepository;
use InvalidArgumentException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: Bus::COMMAND)]
final readonly class RemoveTransportConfigurationHandler
{
    public function __construct(
        private MessageRecipientRepository $repository,
        private UnitOfWork $uow,
    ) {
    }

    public function __invoke(RemoveTransportConfiguration $command): void
    {
        $recipient = $this->repository->getRecipientFromID($command->getRecipientId());
        if (null === $recipient) {
            throw new InvalidArgumentException("Recipient with ID {$command->recipientId} not found.");
        }

        $recipient->removeTransportConfiguration($command->transportKey);

        $this->uow->commit();
    }
}
