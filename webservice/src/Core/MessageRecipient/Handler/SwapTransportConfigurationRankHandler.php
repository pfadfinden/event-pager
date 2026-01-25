<?php

declare(strict_types=1);

namespace App\Core\MessageRecipient\Handler;

use App\Core\Contracts\Bus\Bus;
use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\MessageRecipient\Command\SwapTransportConfigurationRank;
use App\Core\MessageRecipient\Model\AbstractMessageRecipient;
use App\Core\MessageRecipient\Model\RecipientTransportConfiguration;
use App\Core\MessageRecipient\Port\MessageRecipientRepository;
use InvalidArgumentException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: Bus::COMMAND)]
final readonly class SwapTransportConfigurationRankHandler
{
    public function __construct(
        private MessageRecipientRepository $repository,
        private UnitOfWork $uow,
    ) {
    }

    public function __invoke(SwapTransportConfigurationRank $command): void
    {
        $recipient = $this->repository->getRecipientFromID($command->getRecipientId());
        if (!$recipient instanceof AbstractMessageRecipient) {
            throw new InvalidArgumentException("Recipient with ID {$command->recipientId} not found.");
        }

        $config = $recipient->getTransportConfigurationById($command->getConfigId());
        if (!$config instanceof RecipientTransportConfiguration) {
            throw new InvalidArgumentException("Transport configuration with ID '{$command->configId}' not found.");
        }

        // Find the config with the next higher rank (higher rank = evaluated first)
        $configs = array_values($recipient->getTransportConfiguration());

        if ($command->moveUp) {
            $oldRank = $config->getRank();
            $nextHigherConfig = $this->nextHigherConfig($configs, $config);
            if (!$nextHigherConfig instanceof RecipientTransportConfiguration) {
                throw new InvalidArgumentException("Can not find next higher rank compared to '{$command->configId}'");
            }
            $config->setRank($nextHigherConfig->getRank());
            $nextHigherConfig->setRank($oldRank);
        } else {
            $oldRank = $config->getRank();
            $nextLowerConfig = $this->nextLowerConfig($configs, $config);
            if (!$nextLowerConfig instanceof RecipientTransportConfiguration) {
                throw new InvalidArgumentException("Can not find next lower rank compared to '{$command->configId}'");
            }
            $config->setRank($nextLowerConfig->getRank());
            $nextLowerConfig->setRank($oldRank);
        }

        $this->uow->commit();
    }

    /**
     * @param RecipientTransportConfiguration[] $configs
     */
    public function nextHigherConfig(array $configs, RecipientTransportConfiguration $config): ?RecipientTransportConfiguration
    {
        $targetConfig = null;
        foreach ($configs as $c) {
            if ($c->getRank() > $config->getRank() && (null === $targetConfig || $c->getRank() < $targetConfig->getRank())) {
                $targetConfig = $c;
            }
        }

        return $targetConfig;
    }

    /**
     * @param RecipientTransportConfiguration[] $configs
     */
    public function nextLowerConfig(array $configs, RecipientTransportConfiguration $config): ?RecipientTransportConfiguration
    {
        $targetConfig = null;
        foreach ($configs as $c) {
            if ($c->getRank() < $config->getRank() && (null === $targetConfig || $c->getRank() > $targetConfig->getRank())) {
                $targetConfig = $c;
            }
        }

        return $targetConfig;
    }
}
