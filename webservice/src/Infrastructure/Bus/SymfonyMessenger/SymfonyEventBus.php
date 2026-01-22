<?php

declare(strict_types=1);

namespace App\Infrastructure\Bus\SymfonyMessenger;

use App\Core\Contracts\Bus\EventBus;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

final readonly class SymfonyEventBus implements EventBus
{
    public function __construct(private MessageBusInterface $eventBus)
    {
    }

    public function publish(object $event): void
    {
        $this->eventBus->dispatch(new Envelope($event)
            ->with(new DispatchAfterCurrentBusStamp()));
    }
}
