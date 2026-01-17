<?php

declare(strict_types=1);

namespace App\Tests\Core\NtfyTransport;

use App\Core\Contracts\Bus\EventBus;
use App\Core\NtfyTransport\Application\NtfyTransport;
use App\Core\NtfyTransport\Application\NtfyTransportFactory;
use App\Core\NtfyTransport\Exception\NtfySendFailed;
use App\Core\NtfyTransport\Model\NtfyPriority;
use App\Core\NtfyTransport\Model\RecipientConfiguration;
use App\Core\NtfyTransport\Port\NtfyClientInterface;
use App\Core\TransportContract\Model\Message;
use App\Core\TransportContract\Model\MessageRecipient;
use App\Core\TransportContract\Model\OutgoingMessage;
use App\Core\TransportContract\Model\OutgoingMessageEvent;
use App\Core\TransportContract\Model\OutgoingMessageStatus;
use App\Core\TransportContract\Model\Priority;
use App\Core\TransportContract\Model\SystemTransportConfig;
use App\Core\TransportContract\Port\Transport;
use Iterator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

/**
 * Integration test for the NtfyTransport module.
 *
 * Tests the full transport flow from factory to delivery, mocking the NtfyClientInterface.
 */
#[CoversClass(NtfyTransport::class)]
#[CoversClass(NtfyTransportFactory::class)]
#[CoversClass(NtfyPriority::class)]
#[CoversClass(RecipientConfiguration::class)]
#[Group('integration')]
final class NtfyTransportIntegrationTest extends TestCase
{
    private NtfyClientInterface&MockObject $ntfyClientMock;
    private EventBus&MockObject $eventBusMock;

    protected function setUp(): void
    {
        $this->ntfyClientMock = self::createMock(NtfyClientInterface::class);
        $this->eventBusMock = self::createMock(EventBus::class);
    }

    public function testFullTransportFlowWithSuccessfulDelivery(): void
    {
        // Arrange
        $expectedServerUrl = 'https://ntfy.example.com';
        $expectedTopic = 'test-topic';
        $expectedBody = 'Hello from Event Pager!';
        $expectedAccessToken = 'test-access-token';

        $this->ntfyClientMock->expects(self::once())
            ->method('send')
            ->with(
                $expectedServerUrl,
                $expectedTopic,
                $expectedBody,
                NtfyPriority::HIGH,
                $expectedAccessToken,
            );

        // Create transport via factory
        $factory = new NtfyTransportFactory($this->ntfyClientMock, $this->eventBusMock);

        $systemConfig = $this->createSystemConfig([
            'serverUrl' => $expectedServerUrl,
            'accessToken' => $expectedAccessToken,
        ]);

        $transport = $factory->withSystemConfiguration($systemConfig);

        // Verify transport accepts messages
        self::assertTrue($transport->acceptsNewMessages());

        // Create recipient and message
        $recipient = $this->createRecipient($transport, ['topic' => $expectedTopic]);
        $message = $this->createMessage($expectedBody, Priority::HIGH);

        // Verify we can send to this recipient
        self::assertTrue($transport->canSendTo($recipient, $message));

        // Expect transmitted event
        $this->eventBusMock->expects(self::once())
            ->method('publish')
            ->with(self::callback(
                fn (OutgoingMessageEvent $event): bool => OutgoingMessageStatus::TRANSMITTED === $event->status
            ));

        // Act
        $outgoingMessage = OutgoingMessage::for($recipient, $message);
        $transport->send($outgoingMessage);
    }

    public function testFullTransportFlowWithClientError(): void
    {
        // Arrange
        $this->ntfyClientMock->expects(self::once())
            ->method('send')
            ->willThrowException(NtfySendFailed::withReason('Connection failed'));

        $factory = new NtfyTransportFactory($this->ntfyClientMock, $this->eventBusMock);

        $systemConfig = $this->createSystemConfig([
            'serverUrl' => 'https://ntfy.example.com',
        ]);

        $transport = $factory->withSystemConfiguration($systemConfig);

        $recipient = $this->createRecipient($transport, ['topic' => 'test-topic']);
        $message = $this->createMessage('Test message', Priority::DEFAULT);

        // Expect failed event
        $this->eventBusMock->expects(self::once())
            ->method('publish')
            ->with(self::callback(
                fn (OutgoingMessageEvent $event): bool => OutgoingMessageStatus::ERROR === $event->status
            ));

        // Act
        $outgoingMessage = OutgoingMessage::for($recipient, $message);
        $transport->send($outgoingMessage);
    }

    /**
     * @return Iterator<string, array{Priority, NtfyPriority}>
     */
    public static function providePriorityMappings(): Iterator
    {
        yield 'URGENT maps to MAX' => [Priority::URGENT, NtfyPriority::MAX];
        yield 'HIGH maps to HIGH' => [Priority::HIGH, NtfyPriority::HIGH];
        yield 'DEFAULT maps to DEFAULT' => [Priority::DEFAULT, NtfyPriority::DEFAULT];
        yield 'LOW maps to LOW' => [Priority::LOW, NtfyPriority::LOW];
        yield 'MIN maps to MIN' => [Priority::MIN, NtfyPriority::MIN];
    }

    #[DataProvider('providePriorityMappings')]
    public function testPriorityIsMappedCorrectly(Priority $appPriority, NtfyPriority $expectedNtfyPriority): void
    {
        $this->ntfyClientMock->expects(self::once())
            ->method('send')
            ->with(
                self::anything(),
                self::anything(),
                self::anything(),
                $expectedNtfyPriority,
                self::anything(),
            );

        $factory = new NtfyTransportFactory($this->ntfyClientMock, $this->eventBusMock);

        $systemConfig = $this->createSystemConfig(['serverUrl' => 'https://ntfy.sh']);
        $transport = $factory->withSystemConfiguration($systemConfig);

        $recipient = $this->createRecipient($transport, ['topic' => 'test']);
        $message = $this->createMessage('Test', $appPriority);

        $transport->send(OutgoingMessage::for($recipient, $message));
    }

    public function testTransportWithoutAccessToken(): void
    {
        $this->ntfyClientMock->expects(self::once())
            ->method('send')
            ->with(
                'https://ntfy.sh',
                'test',
                'Test',
                NtfyPriority::DEFAULT,
                null, // No access token
            );

        $factory = new NtfyTransportFactory($this->ntfyClientMock, $this->eventBusMock);

        $systemConfig = $this->createSystemConfig([
            'serverUrl' => 'https://ntfy.sh',
            // No access token
        ]);

        $transport = $factory->withSystemConfiguration($systemConfig);

        $recipient = $this->createRecipient($transport, ['topic' => 'test']);
        $message = $this->createMessage('Test');

        $this->eventBusMock->expects(self::once())->method('publish');

        $transport->send(OutgoingMessage::for($recipient, $message));
    }

    public function testTransportRejectsMessagesWhenServerUrlMissing(): void
    {
        $factory = new NtfyTransportFactory($this->ntfyClientMock, $this->eventBusMock);

        $systemConfig = $this->createSystemConfig([]);
        $transport = $factory->withSystemConfiguration($systemConfig);

        self::assertFalse($transport->acceptsNewMessages());
    }

    public function testTransportRejectsRecipientWithoutTopic(): void
    {
        $factory = new NtfyTransportFactory($this->ntfyClientMock, $this->eventBusMock);

        $systemConfig = $this->createSystemConfig(['serverUrl' => 'https://ntfy.sh']);
        $transport = $factory->withSystemConfiguration($systemConfig);

        $recipient = $this->createRecipient($transport, []); // No topic
        $message = $this->createMessage('Test');

        self::assertFalse($transport->canSendTo($recipient, $message));
    }

    /**
     * @param array<string, string> $vendorConfig
     */
    private function createSystemConfig(array $vendorConfig): SystemTransportConfig
    {
        return new readonly class($vendorConfig) implements SystemTransportConfig {
            /**
             * @param array<string, string> $vendorConfig
             */
            public function __construct(private array $vendorConfig)
            {
            }

            public function getKey(): string
            {
                return 'ntfy-integration-test';
            }

            // @phpstan-ignore-next-line missingType.iterableValue (JSON compatible array)
            public function getVendorSpecificConfig(): array
            {
                return $this->vendorConfig;
            }
        };
    }

    /**
     * @param array<string, string> $transportConfig
     */
    private function createRecipient(NtfyTransport $transport, array $transportConfig): MessageRecipient
    {
        return new readonly class($transport, $transportConfig) implements MessageRecipient {
            /**
             * @param array<string, string> $transportConfig
             */
            public function __construct(
                private NtfyTransport $transport,
                private array $transportConfig,
            ) {
            }

            public function getId(): Ulid
            {
                return Ulid::fromString('01JT62N5PE9HBQTEZ1PPE6CJ4F');
            }

            // @phpstan-ignore-next-line missingType.iterableValue (JSON compatible array)
            public function getTransportConfigurationFor(Transport $transport): ?array
            {
                if ($transport === $this->transport) {
                    return $this->transportConfig;
                }

                return null;
            }
        };
    }

    private function createMessage(string $body, Priority $priority = Priority::DEFAULT): Message
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
