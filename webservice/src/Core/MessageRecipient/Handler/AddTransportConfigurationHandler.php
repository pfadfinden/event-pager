<?php

declare(strict_types=1);

namespace App\Core\MessageRecipient\Handler;

use App\Core\Contracts\Bus\Bus;
use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\MessageRecipient\Command\AddTransportConfiguration;
use App\Core\MessageRecipient\Port\MessageRecipientRepository;
use InvalidArgumentException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: Bus::COMMAND)]
final readonly class AddTransportConfigurationHandler
{
    public function __construct(
        private MessageRecipientRepository $repository,
        private UnitOfWork $uow,
    ) {
    }

    public function __invoke(AddTransportConfiguration $command): void
    {
        $recipient = $this->repository->getRecipientFromID($command->getRecipientId());
        if (null === $recipient) {
            throw new InvalidArgumentException("Recipient with ID {$command->recipientId} not found.");
        }

        $config = $recipient->addTransportConfiguration($command->transportKey);
        $config->isEnabled = $command->isEnabled;
        $config->setVendorSpecificConfig($command->vendorSpecificConfig);

        $this->uow->commit();
    }
}
