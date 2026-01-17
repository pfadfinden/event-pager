<?php

declare(strict_types=1);

namespace App\Tests\Core\TelegramTransport\Application;

use App\Core\Contracts\Bus\EventBus;
use App\Core\TelegramTransport\Application\TelegramTransport;
use App\Core\TelegramTransport\Exception\TelegramSendFailed;
use App\Core\TelegramTransport\Port\TelegramClientInterface;
use App\Core\TransportContract\Model\Message;
use App\Core\TransportContract\Model\MessageRecipient;
use App\Core\TransportContract\Model\OutgoingMessage;
use App\Core\TransportContract\Model\OutgoingMessageEvent;
use App\Core\TransportContract\Model\OutgoingMessageStatus;
use App\Core\TransportContract\Model\Priority;
use App\Core\TransportContract\Model\SystemTransportConfig;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[CoversClass(TelegramTransport::class)]
#[Group('unit')]
#[AllowMockObjectsWithoutExpectations]
final class TelegramTransportTest extends TestCase
{
    private SystemTransportConfig&Stub $systemConfigStub;
    private TelegramClientInterface&MockObject $telegramClientMock;
    private EventBus&MockObject $eventBusMock;

    protected function setUp(): void
    {
        $this->systemConfigStub = self::createStub(SystemTransportConfig::class);
        $this->systemConfigStub->method('getKey')->willReturn('telegram-test');

        $this->telegramClientMock = self::createMock(TelegramClientInterface::class);
        $this->eventBusMock = self::createMock(EventBus::class);
    }

    private function createTransport(): TelegramTransport
    {
        return new TelegramTransport(
            $this->systemConfigStub,
            $this->telegramClientMock,
            $this->eventBusMock,
        );
    }

    public function testKeyReturnsConfiguredKey(): void
    {
        $transport = $this->createTransport();

        self::assertSame('telegram-test', $transport->key());
    }

    public function testAcceptsNewMessagesReturnsTrueWhenBotTokenIsConfigured(): void
    {
        $this->systemConfigStub->method('getVendorSpecificConfig')->willReturn([
            'botToken' => '123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11',
        ]);

        $transport = $this->createTransport();

        self::assertTrue($transport->acceptsNewMessages());
    }

    public function testAcceptsNewMessagesReturnsFalseWhenBotTokenIsMissing(): void
    {
        $this->systemConfigStub->method('getVendorSpecificConfig')->willReturn([]);

        $transport = $this->createTransport();

        self::assertFalse($transport->acceptsNewMessages());
    }

    public function testAcceptsNewMessagesReturnsFalseWhenBotTokenIsEmpty(): void
    {
        $this->systemConfigStub->method('getVendorSpecificConfig')->willReturn([
            'botToken' => '',
        ]);

        $transport = $this->createTransport();

        self::assertFalse($transport->acceptsNewMessages());
    }

    public function testAcceptsNewMessagesReturnsFalseWhenVendorConfigIsNull(): void
    {
        $this->systemConfigStub->method('getVendorSpecificConfig')->willReturn(null);

        $transport = $this->createTransport();

        self::assertFalse($transport->acceptsNewMessages());
    }

    public function testCanSendToReturnsTrueWhenRecipientHasChatId(): void
    {
        $transport = $this->createTransport();

        $recipientMock = self::createStub(MessageRecipient::class);
        $recipientMock->method('getTransportConfigurationFor')
            ->with($transport)
            ->willReturn(['chatId' => '123456789']);

        $messageMock = $this->createMessageMock();

        self::assertTrue($transport->canSendTo($recipientMock, $messageMock));
    }

    public function testCanSendToReturnsFalseWhenRecipientHasNoConfig(): void
    {
        $transport = $this->createTransport();

        $recipientMock = self::createStub(MessageRecipient::class);
        $recipientMock->method('getTransportConfigurationFor')
            ->with($transport)
            ->willReturn(null);

        $messageMock = $this->createMessageMock();

        self::assertFalse($transport->canSendTo($recipientMock, $messageMock));
    }

    public function testCanSendToReturnsFalseWhenRecipientHasEmptyChatId(): void
    {
        $transport = $this->createTransport();

        $recipientMock = self::createStub(MessageRecipient::class);
        $recipientMock->method('getTransportConfigurationFor')
            ->with($transport)
            ->willReturn(['chatId' => '']);

        $messageMock = $this->createMessageMock();

        self::assertFalse($transport->canSendTo($recipientMock, $messageMock));
    }

    public function testSendPublishesTransmittedEventOnSuccess(): void
    {
        $this->systemConfigStub->method('getVendorSpecificConfig')->willReturn([
            'botToken' => '123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11',
        ]);

        $this->telegramClientMock->expects(self::once())
            ->method('send')
            ->with(
                '123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11',
                '123456789',
                'Test message',
            );

        $this->eventBusMock->expects(self::once())
            ->method('publish')
            ->with(self::callback(
                fn (OutgoingMessageEvent $event): bool => OutgoingMessageStatus::TRANSMITTED === $event->status
            ));

        $transport = $this->createTransport();

        $recipientMock = self::createStub(MessageRecipient::class);
        $recipientMock->method('getTransportConfigurationFor')
            ->with($transport)
            ->willReturn(['chatId' => '123456789']);

        $messageMock = $this->createMessageMock('Test message');
        $outgoingMessage = OutgoingMessage::for($recipientMock, $messageMock);

        $transport->send($outgoingMessage);
    }

    public function testSendPublishesFailedEventWhenClientThrows(): void
    {
        $this->systemConfigStub->method('getVendorSpecificConfig')->willReturn([
            'botToken' => '123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11',
        ]);

        $this->telegramClientMock->expects(self::once())
            ->method('send')
            ->willThrowException(TelegramSendFailed::withReason('Connection failed'));

        $this->eventBusMock->expects(self::once())
            ->method('publish')
            ->with(self::callback(
                fn (OutgoingMessageEvent $event): bool => OutgoingMessageStatus::ERROR === $event->status
            ));

        $transport = $this->createTransport();

        $recipientMock = self::createStub(MessageRecipient::class);
        $recipientMock->method('getTransportConfigurationFor')
            ->with($transport)
            ->willReturn(['chatId' => '123456789']);

        $messageMock = $this->createMessageMock();
        $outgoingMessage = OutgoingMessage::for($recipientMock, $messageMock);

        $transport->send($outgoingMessage);
    }

    public function testSendPublishesFailedEventWhenRecipientConfigIsMissing(): void
    {
        $this->eventBusMock->expects(self::once())
            ->method('publish')
            ->with(self::callback(
                fn (OutgoingMessageEvent $event): bool => OutgoingMessageStatus::ERROR === $event->status
            ));

        $transport = $this->createTransport();

        $recipientMock = self::createStub(MessageRecipient::class);
        $recipientMock->method('getTransportConfigurationFor')
            ->with($transport)
            ->willReturn(null);

        $messageMock = $this->createMessageMock();
        $outgoingMessage = OutgoingMessage::for($recipientMock, $messageMock);

        $transport->send($outgoingMessage);
    }

    public function testSendPublishesFailedEventWhenBotTokenIsMissing(): void
    {
        $this->systemConfigStub->method('getVendorSpecificConfig')->willReturn([]);

        $this->eventBusMock->expects(self::once())
            ->method('publish')
            ->with(self::callback(
                fn (OutgoingMessageEvent $event): bool => OutgoingMessageStatus::ERROR === $event->status
            ));

        $transport = $this->createTransport();

        $recipientMock = self::createStub(MessageRecipient::class);
        $recipientMock->method('getTransportConfigurationFor')
            ->with($transport)
            ->willReturn(['chatId' => '123456789']);

        $messageMock = $this->createMessageMock();
        $outgoingMessage = OutgoingMessage::for($recipientMock, $messageMock);

        $transport->send($outgoingMessage);
    }

    private function createMessageMock(string $body = 'Test', Priority $priority = Priority::DEFAULT): Message
    {
        return new class($body, $priority) implements Message {
            public function __construct(
                public string $body,
                public Priority $priority,
            ) {
            }

            public Ulid $messageId {
                get {
                    return Ulid::fromString('01JT62N5PE9HBQTEZ1PPE6CJ4F');
                }
            }
        };
    }
}
