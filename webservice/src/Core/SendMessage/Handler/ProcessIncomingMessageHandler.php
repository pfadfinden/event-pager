<?php

declare(strict_types=1);

namespace App\Core\SendMessage\Handler;

use App\Core\Contracts\Bus\Bus;
use App\Core\MessageRecipient\Model\AbstractMessageRecipient;
use App\Core\MessageRecipient\Port\MessageRecipientRepository;
use App\Core\SendMessage\Command\ProcessIncomingMessage;
use App\Core\SendMessage\Model\IncomingMessage;
use App\Core\SendMessage\Port\IncomingMessageRepository;
use App\Core\TransportContract\Model\Message;
use App\Core\TransportContract\Model\MessageRecipient;
use App\Core\TransportContract\Model\OutgoingMessage;
use App\Core\TransportContract\Model\Priority;
use App\Core\TransportContract\Port\Transport;
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
    ) {
    }

    public function __invoke(ProcessIncomingMessage $cmd): void
    {
        $incomingMessage = $this->incomingMessageRepository->getWithId(Ulid::fromString($cmd->id));

        if (null === $incomingMessage) {
            throw new RuntimeException('Incoming message not found');
        }

        // TODO Replace with more advanced addressing algorithm:
        $message = $this->asMessageDto($incomingMessage);

        foreach ($incomingMessage->to as $to) {
            $messageRecipient = $this->getMessageRecipient($to);
            if (null === $messageRecipient) {
                continue; // recipient deactivated since sending or wrong api call
            }
            foreach ($this->transportManager->activeTransports() as $transport) {
                if ($transport->canSendTo($messageRecipient, $message)) {
                    $transport->send(OutgoingMessage::for($messageRecipient, $message));
                }
            }
        }
    }

    public function asMessageDto(IncomingMessage $incomingMessage): Message
    {
        return new readonly class($incomingMessage->messageId, $incomingMessage->content, Priority::from($incomingMessage->priority * 10)) implements Message {
            public function __construct(
                public Ulid $id,
                public string $body,
                public Priority $priority)
            {
            }
        };
    }

    public function getMessageRecipient(Ulid $to): ?MessageRecipient
    {
        $recipient = $this->messageRecipientRepository->getRecipientFromID($to);

        if (null === $recipient) {
            return null;
        }

        return new readonly class($recipient) implements MessageRecipient {
            public function __construct(private AbstractMessageRecipient $recipient)
            {
            }

            public function getId(): Ulid
            {
                return $this->recipient->id;
            }

            // @phpstan-ignore missingType.iterableValue (JSON compatible array)
            public function getTransportConfigurationFor(Transport $transport): ?array
            {
                return null;
            }
        };
    }
}
