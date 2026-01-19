<?php

declare(strict_types=1);

namespace App\Core\PredefinedMessages\Application;

use App\Core\Contracts\Bus\Bus;
use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\PredefinedMessages\Command\DeletePredefinedMessage;
use App\Core\PredefinedMessages\Port\PredefinedMessageRepository;
use InvalidArgumentException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: Bus::COMMAND)]
final readonly class DeletePredefinedMessageHandler
{
    public function __construct(
        private PredefinedMessageRepository $repository,
        private UnitOfWork $uow,
    ) {
    }

    public function __invoke(DeletePredefinedMessage $command): void
    {
        $predefinedMessage = $this->repository->getById($command->getId());

        if (null === $predefinedMessage) {
            throw new InvalidArgumentException("Predefined message with ID {$command->id} not found.");
        }

        $this->repository->remove($predefinedMessage);
        $this->uow->commit();
    }
}
