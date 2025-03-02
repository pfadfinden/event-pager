<?php

declare(strict_types=1);

namespace App\Core\SendMessage\Handler;

use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\SendMessage\Command\SendMessage;
use App\Core\SendMessage\Model\IncomingMessage;
use App\Core\SendMessage\Port\IncomingMessageRepository;
use Symfony\Component\Uid\Ulid;

final readonly class SendMessageHandler
{
    public function __construct(
        private readonly IncomingMessageRepository $repository,
        private readonly UnitOfWork $uow,
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

        // TODO call addressing algorithm
    }
}
