<?php

declare(strict_types=1);

namespace App\Core\MessageRecipient\Handler;

use App\Core\Contracts\Bus\Bus;
use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\MessageRecipient\Command\UpdateTransportConfiguration;
use App\Core\MessageRecipient\Model\AbstractMessageRecipient;
use App\Core\MessageRecipient\Model\RecipientTransportConfiguration;
use App\Core\MessageRecipient\Port\MessageRecipientRepository;
use InvalidArgumentException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: Bus::COMMAND)]
final readonly class UpdateTransportConfigurationHandler
{
    public function __construct(
        private MessageRecipientRepository $repository,
        private UnitOfWork $uow,
    ) {
    }

    public function __invoke(UpdateTransportConfiguration $command): void
    {
        $recipient = $this->repository->getRecipientFromID($command->getRecipientId());
        if (!$recipient instanceof AbstractMessageRecipient) {
            throw new InvalidArgumentException("Recipient with ID {$command->recipientId} not found.");
        }

        $config = $recipient->getTransportConfigurationByKey($command->transportKey);
        if (!$config instanceof RecipientTransportConfiguration) {
            throw new InvalidArgumentException("Transport configuration for key '{$command->transportKey}' not found.");
        }

        $config->isEnabled = $command->isEnabled;
        $config->setVendorSpecificConfig($command->vendorSpecificConfig);

        $this->uow->commit();
    }
}
