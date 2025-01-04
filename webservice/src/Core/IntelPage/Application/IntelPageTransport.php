<?php

declare(strict_types=1);

namespace App\Core\IntelPage\Application;

use App\Core\Contracts\Bus\CommandBus;
use App\Core\Contracts\Bus\EventBus;
use App\Core\Contracts\Bus\QueryBus;
use App\Core\IntelPage\Command\QueueMessage;
use App\Core\IntelPage\Exception\IntelPageMessageTooLong;
use App\Core\IntelPage\Model\CapCode;
use App\Core\IntelPage\Model\Pager;
use App\Core\IntelPage\Model\PagerMessage;
use App\Core\IntelPage\Model\RecipientConfiguration;
use App\Core\IntelPage\Query\ChannelCapCodeById;
use App\Core\IntelPage\Query\PagerByRecipient;
use App\Core\TransportContract\Model\Message;
use App\Core\TransportContract\Model\MessageRecipient;
use App\Core\TransportContract\Model\OutgoingMessage;
use App\Core\TransportContract\Model\OutgoingMessageEvent;
use App\Core\TransportContract\Model\Priority;
use App\Core\TransportContract\Model\SystemTransportConfig;
use App\Core\TransportContract\Port\Transport;
use function strlen;

/**
 * Queues a message to be sent through an IntelPage appliance to hardware
 * pager based on the recipient & pager configuration.
 *
 * @internal responsibility of the class itself is to find the right cap code,
 * everything else is delegated
 */
final readonly class IntelPageTransport implements Transport
{
    public function __construct(
        private SystemTransportConfig $config,
        private QueryBus $queryBus,
        private CommandBus $commandBus,
        private EventBus $eventBus,
    ) {
    }

    public function key(): string
    {
        return $this->config->getKey();
    }

    public function acceptsNewMessages(): bool
    {
        /* At this time, there is no option to configure the transport from
         * the application side. It is to be expected, that the operations
         * responsible configured the transport correctly, therefore (for now)
         * this method always returns true.
         */
        return true;
    }

    public function canSendTo(MessageRecipient $recipient, Message $incomingMessage): bool
    {
        $this->validateMessageLength($incomingMessage->body);

        $config = $recipient->getTransportConfigurationFor($this);
        if (null === $config) {
            return false;
        }
        $recipientConfiguration = new RecipientConfiguration($config);

        if ($recipientConfiguration->hasChannelConfiguration()) {
            // -- should send to channel, can do so only if channel cap code was found in database:
            return $this->channelCapCode($recipientConfiguration) instanceof CapCode;
        }

        // -- should send to individual pager
        $pager = $this->getPager($recipient);

        return $pager instanceof Pager // can send only if pager was found
            && $pager->isActivated()  //  and pager is active
            && $this->selectPagerCapBasedOnPriority(
                $incomingMessage->priority, $recipientConfiguration, $pager
            ) instanceof CapCode; // and cap code was assigned to pager
    }

    public function send(OutgoingMessage $message): void
    {
        $capCode = $this->tryToRetrieveCapCode($message);
        if (!$capCode instanceof CapCode) {
            // -- this should never happen, reasons to land here:
            //    a) something changed between calls ::canSendTo() and ::send()
            //    b) developer failed to make all relevant checks in ::canSendTo()
            $this->failedToSend($message);

            return;
        }

        $this->commandBus->do(QueueMessage::with(
            $message->id,
            $this->key(),
            $capCode,
            $message->incomingMessage->body,
            $message->incomingMessage->priority->value,
            $message->incomingMessage->messageId,
        ));
    }

    private function tryToRetrieveCapCode(OutgoingMessage $message): ?CapCode
    {
        $config = $message->recipient->getTransportConfigurationFor($this);
        if (null === $config) {
            return null;
        }
        $recipientConfiguration = new RecipientConfiguration($config);

        if ($recipientConfiguration->hasChannelConfiguration()) {
            return $this->channelCapCode($recipientConfiguration);
        }

        $pager = $this->getPager($message->recipient);

        return $pager instanceof Pager ? $this->selectPagerCapBasedOnPriority($message->incomingMessage->priority, $recipientConfiguration, $pager) : null;
    }

    private function getPager(MessageRecipient $recipient): ?Pager
    {
        return $this->queryBus->get(PagerByRecipient::withId($recipient->getId()->toString()));
    }

    private function validateMessageLength(string $messageString): void
    {
        if (($len = strlen($messageString)) > PagerMessage::MAX_LENGTH) {
            throw IntelPageMessageTooLong::withLength($len);
        }
    }

    public function channelCapCode(RecipientConfiguration $recipientConfiguration): ?CapCode
    {
        return $this->queryBus->get(new ChannelCapCodeById($recipientConfiguration->channelId()));
    }

    /**
     * HIGH-RISK for Code Injection!!!
     *
     * @return array{0: string, 1: int}
     */
    public function configuredTransmitter(): array
    {
        return [
            $this->config->getVendorSpecificConfig()['transmitterHost'] ?? null,
            $this->config->getVendorSpecificConfig()['transmitterPort'] ?? null,
        ];
    }

    private function selectPagerCapBasedOnPriority(
        Priority $messagePriority, RecipientConfiguration $recipientConfiguration, Pager $pager,
    ): ?CapCode {
        if ($messagePriority->isHigherOrEqual($recipientConfiguration->alertFromPriority())) {
            return $pager->individualAlertCap();
        }

        return $pager->individualNonAlertCap();
    }

    /**
     * Publish failure event to EventBus.
     *
     * @see OutgoingMessageEvent
     */
    private function failedToSend(OutgoingMessage $message): void
    {
        $this->eventBus->publish(OutgoingMessageEvent::failedToQueue($message->id, $message->incomingMessage->messageId));
    }
}
