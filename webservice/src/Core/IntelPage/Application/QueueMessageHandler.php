<?php

declare(strict_types=1);

namespace App\Core\IntelPage\Application;

use App\Core\Contracts\Bus\Bus;
use App\Core\Contracts\Bus\EventBus;
use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\IntelPage\Command\QueueMessage;
use App\Core\IntelPage\Model\PagerMessage;
use App\Core\IntelPage\Port\PagerMessageRepository;
use App\Core\TransportContract\Model\OutgoingMessageEvent;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: Bus::COMMAND)]
final readonly class QueueMessageHandler
{
    public function __construct(
        private PagerMessageRepository $pagerMessageRepository,
        private UnitOfWork $uow,
        private EventBus $eventBus,
    ) {
    }

    public function __invoke(QueueMessage $cmd): void
    {
        $pagerMessage = PagerMessage::new($cmd->id, $cmd->transport, $cmd->capCode, $cmd->message, $cmd->priority);
        $this->pagerMessageRepository->add($pagerMessage);
        $this->uow->commit();

        $this->eventBus->publish(OutgoingMessageEvent::queued($cmd->incomingMessageId, $cmd->id));
    }
}
