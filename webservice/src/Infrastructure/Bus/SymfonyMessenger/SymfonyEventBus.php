<?php

declare(strict_types=1);

namespace App\Infrastructure\Bus\SymfonyMessenger;

use App\Core\Contracts\Bus\EventBus;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class SymfonyEventBus implements EventBus
{
    public function __construct(private MessageBusInterface $eventBus)
    {
    }

    public function publish(object $event): void
    {
        $this->eventBus->dispatch($event);
    }
}
