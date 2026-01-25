<?php

declare(strict_types=1);

namespace App\Core\SendMessage\Handler;

use App\Core\Contracts\Bus\Bus;
use App\Core\Contracts\Bus\EventBus;
use App\Core\MessageRecipient\Model\AbstractMessageRecipient;
use App\Core\MessageRecipient\Model\MessageRecipient;
use App\Core\MessageRecipient\Port\MessageRecipientRepository;
use App\Core\SendMessage\Application\MessageAddressing\RecipientResolver;
use App\Core\SendMessage\Command\ProcessIncomingMessage;
use App\Core\SendMessage\Model\IncomingMessage;
use App\Core\SendMessage\Model\MessageAddressing\SelectedTransport;
use App\Core\SendMessage\Port\IncomingMessageRepository;
use App\Core\TransportContract\Model\Message;
use App\Core\TransportContract\Model\NewOutgoingMessageInitiated;
use App\Core\TransportContract\Model\OutgoingMessage;
use App\Core\TransportContract\Model\Priority;
use RuntimeException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Uid\Ulid;

/**
 * This processor executes the message addressing algorithm for new incoming messages.
 * It might be executed asynchronous.
 */
#[AsMessageHandler(bus: Bus::COMMAND)]
final readonly class ProcessIncomingMessageHandler
{
    public function __construct(
        private IncomingMessageRepository $incomingMessageRepository,
        private MessageRecipientRepository $messageRecipientRepository,
        private RecipientResolver $recipientResolver,
        private EventBus $eventBus,
    ) {
    }

    public function __invoke(ProcessIncomingMessage $cmd): void
    {
        $incomingMessage = $this->incomingMessageRepository->getWithId(Ulid::fromString($cmd->id));

        if (!$incomingMessage instanceof IncomingMessage) {
            throw new RuntimeException('Incoming message not found');
        }

        $message = $this->asMessageDto($incomingMessage);
        $recipients = $this->retrieveRecipients($incomingMessage);

        $results = $this->recipientResolver->resolve(
            $recipients,
            $message,
        );

        // TODO NEXT-STEP we need to group messages by transport first and then push all of it to the transport at once to enable deduplication / batch operation etc.
        foreach ($results as $result) {
            $recipient = $result->recipient;

            if (!$result->hasSelectedTransports() && !$result->hasMembersToExpand()) {
                // this was a dead end and no messages was delivered for this recipient
                $this->eventBus->publish(NewOutgoingMessageInitiated::for(OutgoingMessage::failure($recipient, $message), true));
                continue;
            }

            foreach ($result->selectedTransports as $selectedTransport) {
                $this->sendOneTo($recipient, $message, $selectedTransport);
            }
        }
    }

    /**
     * @return iterable<AbstractMessageRecipient>
     */
    private function retrieveRecipients(IncomingMessage $incomingMessage): iterable
    {
        foreach ($incomingMessage->to as $to) {
            $messageRecipient = $this->messageRecipientRepository->getRecipientFromID($to);
            if ($messageRecipient instanceof AbstractMessageRecipient) {
                yield $messageRecipient;
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

    private function sendOneTo(MessageRecipient $recipient, Message $message, SelectedTransport $selectedTransport): void
    {
        $outgoingMessage = OutgoingMessage::for($recipient, $message, $selectedTransport->transport);
        $this->eventBus->publish(NewOutgoingMessageInitiated::for($outgoingMessage));
        $selectedTransport->transport->send($outgoingMessage);
    }
}
