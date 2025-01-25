<?php

declare(strict_types=1);

namespace App\Infrastructure\Bus\SymfonyMessenger;

use App\Core\Contracts\Bus\CommandBus;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class SymfonyCommandBus implements CommandBus
{
    public function __construct(private MessageBusInterface $commandBus)
    {
    }

    public function do(object $command): void
    {
        $this->commandBus->dispatch($command);
    }
}
