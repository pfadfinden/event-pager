<?php

declare(strict_types=1);

namespace App\Core\PredefinedMessages\Application;

use App\Core\Contracts\Bus\Bus;
use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\PredefinedMessages\Command\EditPredefinedMessage;
use App\Core\PredefinedMessages\Port\PredefinedMessageRepository;
use InvalidArgumentException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: Bus::COMMAND)]
final readonly class EditPredefinedMessageHandler
{
    public function __construct(
        private PredefinedMessageRepository $repository,
        private UnitOfWork $uow,
    ) {
    }

    public function __invoke(EditPredefinedMessage $command): void
    {
        $predefinedMessage = $this->repository->getById($command->getId());

        if (null === $predefinedMessage) {
            throw new InvalidArgumentException("Predefined message with ID {$command->id} not found.");
        }

        $predefinedMessage->setTitle($command->title);
        $predefinedMessage->setMessageContent($command->messageContent);
        $predefinedMessage->setPriority($command->priority);
        $predefinedMessage->setRecipientIds($command->recipientIds);
        $predefinedMessage->setIsFavorite($command->isFavorite);
        $predefinedMessage->setSortOrder($command->sortOrder);
        $predefinedMessage->setIsEnabled($command->isEnabled);

        $this->uow->commit();
    }
}
