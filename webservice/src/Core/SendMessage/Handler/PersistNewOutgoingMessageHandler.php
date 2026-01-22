<?php

declare(strict_types=1);

namespace App\Core\SendMessage\Handler;

use App\Core\Contracts\Bus\Bus;
use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\SendMessage\Model\OutgoingMessageEventRecord;
use App\Core\SendMessage\Port\OutgoingMessageEventRepository;
use App\Core\TransportContract\Model\NewOutgoingMessageInitiated;
use App\Core\TransportContract\Model\OutgoingMessageStatus;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: Bus::EVENT)]
final readonly class PersistNewOutgoingMessageHandler
{
    public function __construct(
        private OutgoingMessageEventRepository $repository,
        private UnitOfWork $uow,
    ) {
    }

    public function __invoke(NewOutgoingMessageInitiated $event): void
    {
        $record = OutgoingMessageEventRecord::newMessage(
            $event->outgoingMessageId,
            $event->at,
            $event->failed ? OutgoingMessageStatus::NOT_INITIATED : OutgoingMessageStatus::INITIATED,
            $event->incomingMessageId,
            $event->recipientId,
            $event->transportKey
        );

        $this->repository->add($record);
        $this->uow->commit();
    }
}
