<?php

declare(strict_types=1);

namespace App\Core\SendMessage\Handler;

use App\Core\Contracts\Bus\Bus;
use App\Core\Contracts\Bus\EventBus;
use App\Core\MessageRecipient\Model\AbstractMessageRecipient;
use App\Core\MessageRecipient\Port\MessageRecipientRepository;
use App\Core\SendMessage\Command\ProcessIncomingMessage;
use App\Core\SendMessage\Model\IncomingMessage;
use App\Core\SendMessage\Port\IncomingMessageRepository;
use App\Core\TransportContract\Model\Message;
use App\Core\TransportContract\Model\NewOutgoingMessageInitiated;
use App\Core\TransportContract\Model\OutgoingMessage;
use App\Core\TransportContract\Model\Priority;
use App\Core\TransportContract\Port\TransportManager;
use RuntimeException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Uid\Ulid;

/**
 * This processor executes the message addressing algorithm for new incoming messages.
 * It might be executed asynchronous.
 *
 * At this time, an incomplete, non-optimized, non-tested addressing algorithm is included to allow messages to be sent.
 */
#[AsMessageHandler(bus: Bus::COMMAND)]
final readonly class ProcessIncomingMessageHandler
{
    public function __construct(
        private IncomingMessageRepository $incomingMessageRepository,
        private MessageRecipientRepository $messageRecipientRepository,
        private TransportManager $transportManager,
        private EventBus $eventBus,
    ) {
    }

    public function __invoke(ProcessIncomingMessage $cmd): void
    {
        $incomingMessage = $this->incomingMessageRepository->getWithId(Ulid::fromString($cmd->id));

        if (!$incomingMessage instanceof IncomingMessage) {
            throw new RuntimeException('Incoming message not found');
        }

        // TODO Replace with more advanced addressing algorithm:
        $message = $this->asMessageDto($incomingMessage);

        foreach ($incomingMessage->to as $to) {
            $messageRecipient = $this->messageRecipientRepository->getRecipientFromID($to);
            if (!$messageRecipient instanceof AbstractMessageRecipient) {
                continue; // recipient deactivated since sending or wrong api call
            }
            foreach ($this->transportManager->activeTransports() as $transport) {
                if ($transport->canSendTo($messageRecipient, $message)) {
                    $outgoingMessage = OutgoingMessage::for($messageRecipient, $message);
                    $this->eventBus->publish(NewOutgoingMessageInitiated::for($outgoingMessage));
                    $transport->send($outgoingMessage);
                }
            }
        }
    }

    public function asMessageDto(IncomingMessage $incomingMessage): Message
    {
        return new readonly class($incomingMessage->messageId, $incomingMessage->content, Priority::from($incomingMessage->priority * 10)) implements Message {
            public function __construct(
                public Ulid $messageId,
                public string $body,
                public Priority $priority)
            {
            }
        };
    }
}
