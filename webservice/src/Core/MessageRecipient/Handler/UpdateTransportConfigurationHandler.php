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

        $config = $recipient->getTransportConfigurationById($command->getConfigId());
        if (!$config instanceof RecipientTransportConfiguration) {
            throw new InvalidArgumentException("Transport configuration with ID '{$command->configId}' not found.");
        }

        $config->isEnabled = $command->isEnabled;
        $config->setVendorSpecificConfig($command->vendorSpecificConfig);
        $config->setSelectionExpression($command->selectionExpression);
        $config->setContinueInHierarchy($command->continueInHierarchy);
        $config->setEvaluateOtherTransportConfigurations($command->evaluateOtherTransportConfigurations);

        if (null !== $command->rank) {
            $config->setRank($command->rank);
        }

        $this->uow->commit();
    }
}
