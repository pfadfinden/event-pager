<?php

declare(strict_types=1);

namespace App\Core\SendMessage\Handler;

use App\Core\Contracts\Bus\Bus;
use App\Core\Contracts\Bus\CommandBus;
use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\SendMessage\Command\ProcessIncomingMessage;
use App\Core\SendMessage\Command\SendMessage;
use App\Core\SendMessage\Model\IncomingMessage;
use App\Core\SendMessage\Port\IncomingMessageRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Uid\Ulid;

#[AsMessageHandler(bus: Bus::COMMAND)]
final readonly class SendMessageHandler
{
    public function __construct(
        private IncomingMessageRepository $repository,
        private UnitOfWork $uow,
        private CommandBus $commandBus,
    ) {
    }

    public function __invoke(SendMessage $cmd): void
    {
        $incomingMessage = IncomingMessage::new(
            Ulid::fromString($cmd->by),
            array_map(fn ($str) => Ulid::fromString($str), $cmd->to),
            $cmd->message,
            $cmd->priority,
        );

        $this->repository->add($incomingMessage);
        $this->uow->commit();

        $this->commandBus->do(new ProcessIncomingMessage($incomingMessage->messageId->toString()));
    }
}
