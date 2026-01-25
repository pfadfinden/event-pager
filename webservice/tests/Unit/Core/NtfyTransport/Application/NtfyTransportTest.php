<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\NtfyTransport\Application;

use App\Core\Contracts\Bus\EventBus;
use App\Core\NtfyTransport\Application\NtfyTransport;
use App\Core\NtfyTransport\Exception\NtfySendFailed;
use App\Core\NtfyTransport\Model\NtfyPriority;
use App\Core\NtfyTransport\Port\NtfyClientInterface;
use App\Core\TransportContract\Model\Message;
use App\Core\TransportContract\Model\MessageRecipient;
use App\Core\TransportContract\Model\OutgoingMessage;
use App\Core\TransportContract\Model\OutgoingMessageEvent;
use App\Core\TransportContract\Model\OutgoingMessageStatus;
use App\Core\TransportContract\Model\Priority;
use App\Core\TransportContract\Model\SystemTransportConfig;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[CoversClass(NtfyTransport::class)]
#[AllowMockObjectsWithoutExpectations]
final class NtfyTransportTest extends TestCase
{
    private SystemTransportConfig&MockObject $systemConfigMock;
    private NtfyClientInterface&MockObject $ntfyClientMock;
    private EventBus&MockObject $eventBusMock;

    protected function setUp(): void
    {
        $this->systemConfigMock = self::createMock(SystemTransportConfig::class);
        $this->systemConfigMock->method('getKey')->willReturn('ntfy-test');

        $this->ntfyClientMock = self::createMock(NtfyClientInterface::class);
        $this->eventBusMock = self::createMock(EventBus::class);
    }

    private function createTransport(): NtfyTransport
    {
        return new NtfyTransport(
            $this->systemConfigMock,
            $this->ntfyClientMock,
            $this->eventBusMock,
        );
    }

    public function testKeyReturnsConfiguredKey(): void
    {
        $transport = $this->createTransport();

        self::assertSame('ntfy-test', $transport->key());
    }

    public function testAcceptsNewMessagesReturnsTrueWhenServerUrlIsConfigured(): void
    {
        $this->systemConfigMock->method('getVendorSpecificConfig')->willReturn([
            'serverUrl' => 'https://ntfy.sh',
        ]);

        $transport = $this->createTransport();

        self::assertTrue($transport->acceptsNewMessages());
    }

    public function testAcceptsNewMessagesReturnsFalseWhenServerUrlIsMissing(): void
    {
        $this->systemConfigMock->method('getVendorSpecificConfig')->willReturn([]);

        $transport = $this->createTransport();

        self::assertFalse($transport->acceptsNewMessages());
    }

    public function testAcceptsNewMessagesReturnsFalseWhenServerUrlIsEmpty(): void
    {
        $this->systemConfigMock->method('getVendorSpecificConfig')->willReturn([
            'serverUrl' => '',
        ]);

        $transport = $this->createTransport();

        self::assertFalse($transport->acceptsNewMessages());
    }

    public function testAcceptsNewMessagesReturnsFalseWhenVendorConfigIsNull(): void
    {
        $this->systemConfigMock->method('getVendorSpecificConfig')->willReturn(null);

        $transport = $this->createTransport();

        self::assertFalse($transport->acceptsNewMessages());
    }

    public function testCanSendToReturnsTrueWhenRecipientHasTopic(): void
    {
        $transport = $this->createTransport();
        $recipientMock = self::createStub(MessageRecipient::class);
        $messageMock = $this->createMessageMock();
        $config = ['topic' => 'my-alerts'];

        self::assertTrue($transport->canSendTo($recipientMock, $messageMock, $config));
    }

    public function testCanSendToReturnsFalseWhenRecipientHasNoConfig(): void
    {
        $transport = $this->createTransport();
        $recipientMock = self::createStub(MessageRecipient::class);
        $messageMock = $this->createMessageMock();

        self::assertFalse($transport->canSendTo($recipientMock, $messageMock, null));
    }

    public function testCanSendToReturnsFalseWhenRecipientHasEmptyTopic(): void
    {
        $transport = $this->createTransport();
        $recipientMock = self::createStub(MessageRecipient::class);
        $messageMock = $this->createMessageMock();
        $config = ['topic' => ''];

        self::assertFalse($transport->canSendTo($recipientMock, $messageMock, $config));
    }

    public function testSendPublishesTransmittedEventOnSuccess(): void
    {
        $this->systemConfigMock->method('getVendorSpecificConfig')->willReturn([
            'serverUrl' => 'https://ntfy.sh',
            'accessToken' => 'test-token',
        ]);

        $this->ntfyClientMock->expects(self::once())
            ->method('send')
            ->with(
                'https://ntfy.sh',
                'my-alerts',
                'Test message',
                NtfyPriority::DEFAULT,
                'test-token',
            );

        $this->eventBusMock->expects(self::once())
            ->method('publish')
            ->with(self::callback(
                fn (OutgoingMessageEvent $event): bool => OutgoingMessageStatus::TRANSMITTED === $event->status
            ));

        $transport = $this->createTransport();

        $recipientMock = self::createMock(MessageRecipient::class);
        $recipientMock->method('getTransportConfigurationFor')
            ->with($transport)
            ->willReturn(['topic' => 'my-alerts']);

        $messageMock = $this->createMessageMock('Test message');
        $outgoingMessage = OutgoingMessage::for($recipientMock, $messageMock, $transport);

        $transport->send($outgoingMessage);
    }

    public function testSendPublishesFailedEventWhenClientThrows(): void
    {
        $this->systemConfigMock->method('getVendorSpecificConfig')->willReturn([
            'serverUrl' => 'https://ntfy.sh',
        ]);

        $this->ntfyClientMock->expects(self::once())
            ->method('send')
            ->willThrowException(NtfySendFailed::withReason('Connection failed'));

        $this->eventBusMock->expects(self::once())
            ->method('publish')
            ->with(self::callback(
                fn (OutgoingMessageEvent $event): bool => OutgoingMessageStatus::ERROR === $event->status
            ));

        $transport = $this->createTransport();

        $recipientMock = self::createMock(MessageRecipient::class);
        $recipientMock->method('getTransportConfigurationFor')
            ->with($transport)
            ->willReturn(['topic' => 'my-alerts']);

        $messageMock = $this->createMessageMock();
        $outgoingMessage = OutgoingMessage::for($recipientMock, $messageMock, $transport);

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

        $recipientMock = self::createMock(MessageRecipient::class);
        $recipientMock->method('getTransportConfigurationFor')
            ->with($transport)
            ->willReturn(null);

        $messageMock = $this->createMessageMock();
        $outgoingMessage = OutgoingMessage::for($recipientMock, $messageMock, $transport);

        $transport->send($outgoingMessage);
    }

    public function testSendPublishesFailedEventWhenServerUrlIsMissing(): void
    {
        $this->systemConfigMock->method('getVendorSpecificConfig')->willReturn([]);

        $this->eventBusMock->expects(self::once())
            ->method('publish')
            ->with(self::callback(
                fn (OutgoingMessageEvent $event): bool => OutgoingMessageStatus::ERROR === $event->status
            ));

        $transport = $this->createTransport();

        $recipientMock = self::createMock(MessageRecipient::class);
        $recipientMock->method('getTransportConfigurationFor')
            ->with($transport)
            ->willReturn(['topic' => 'my-alerts']);

        $messageMock = $this->createMessageMock();
        $outgoingMessage = OutgoingMessage::for($recipientMock, $messageMock, $transport);

        $transport->send($outgoingMessage);
    }

    public function testSendWithoutAccessToken(): void
    {
        $this->systemConfigMock->method('getVendorSpecificConfig')->willReturn([
            'serverUrl' => 'https://ntfy.sh',
        ]);

        $this->ntfyClientMock->expects(self::once())
            ->method('send')
            ->with(
                'https://ntfy.sh',
                'my-alerts',
                'Test message',
                NtfyPriority::DEFAULT,
                null,
            );

        $this->eventBusMock->expects(self::once())
            ->method('publish');

        $transport = $this->createTransport();

        $recipientMock = self::createMock(MessageRecipient::class);
        $recipientMock->method('getTransportConfigurationFor')
            ->with($transport)
            ->willReturn(['topic' => 'my-alerts']);

        $messageMock = $this->createMessageMock('Test message');
        $outgoingMessage = OutgoingMessage::for($recipientMock, $messageMock, $transport);

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
