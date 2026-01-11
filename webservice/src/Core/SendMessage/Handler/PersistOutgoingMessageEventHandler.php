<?php

declare(strict_types=1);

namespace App\Core\SendMessage\Handler;

use App\Core\Contracts\Bus\Bus;
use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\SendMessage\Model\OutgoingMessageEventRecord;
use App\Core\SendMessage\Port\OutgoingMessageEventRepository;
use App\Core\TransportContract\Model\OutgoingMessageEvent;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: Bus::EVENT)]
final readonly class PersistOutgoingMessageEventHandler
{
    public function __construct(
        private OutgoingMessageEventRepository $repository,
        private UnitOfWork $uow,
    ) {
    }

    public function __invoke(OutgoingMessageEvent $event): void
    {
        $record = OutgoingMessageEventRecord::create(
            $event->outgoingMessageId,
            $event->at,
            $event->status
        );

        $this->repository->add($record);
        $this->uow->commit();
    }
}
