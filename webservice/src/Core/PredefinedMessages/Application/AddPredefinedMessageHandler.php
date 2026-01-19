<?php

declare(strict_types=1);

namespace App\Core\PredefinedMessages\Application;

use App\Core\Contracts\Bus\Bus;
use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\PredefinedMessages\Command\AddPredefinedMessage;
use App\Core\PredefinedMessages\Model\PredefinedMessage;
use App\Core\PredefinedMessages\Port\PredefinedMessageRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: Bus::COMMAND)]
final readonly class AddPredefinedMessageHandler
{
    public function __construct(
        private PredefinedMessageRepository $repository,
        private UnitOfWork $uow,
    ) {
    }

    public function __invoke(AddPredefinedMessage $command): void
    {
        $predefinedMessage = new PredefinedMessage(
            $command->title,
            $command->messageContent,
            $command->priority,
            $command->recipientIds,
            $command->isFavorite,
            $command->sortOrder,
            $command->isEnabled,
        );

        $this->repository->add($predefinedMessage);
        $this->uow->commit();
    }
}
