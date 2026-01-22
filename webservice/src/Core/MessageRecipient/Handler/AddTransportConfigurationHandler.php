<?php

declare(strict_types=1);

namespace App\Core\MessageRecipient\Handler;

use App\Core\Contracts\Bus\Bus;
use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\MessageRecipient\Command\AddTransportConfiguration;
use App\Core\MessageRecipient\Model\AbstractMessageRecipient;
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
        if (!$recipient instanceof AbstractMessageRecipient) {
            throw new InvalidArgumentException("Recipient with ID {$command->recipientId} not found.");
        }

        $config = $recipient->addTransportConfiguration($command->transportKey);
        $config->isEnabled = $command->isEnabled;
        $config->setVendorSpecificConfig($command->vendorSpecificConfig);
        $config->setSelectionExpression($command->selectionExpression);
        $config->setContinueInHierarchy($command->continueInHierarchy);
        $config->setEvaluateOtherTransportConfigurations($command->evaluateOtherTransportConfigurations);

        // Override auto-assigned rank if explicitly provided
        if (null !== $command->rank) {
            $config->setRank($command->rank);
        }

        $this->uow->commit();
    }
}
