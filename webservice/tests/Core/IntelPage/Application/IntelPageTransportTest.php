<?php

declare(strict_types=1);

namespace App\Tests\Core\IntelPage\Application;

use App\Core\Contracts\Bus\CommandBus;
use App\Core\Contracts\Bus\EventBus;
use App\Core\Contracts\Bus\QueryBus;
use App\Core\IntelPage\Application\IntelPageTransport;
use App\Core\IntelPage\Command\QueueMessage;
use App\Core\IntelPage\Exception\IntelPageMessageTooLong;
use App\Core\IntelPage\Model\CapCode;
use App\Core\IntelPage\Model\Pager;
use App\Core\IntelPage\Query\ChannelCapCodeById;
use App\Core\IntelPage\Query\PagerByRecipient;
use App\Core\TransportContract\Model\Message;
use App\Core\TransportContract\Model\MessageRecipient;
use App\Core\TransportContract\Model\OutgoingMessage;
use App\Core\TransportContract\Model\OutgoingMessageEvent;
use App\Core\TransportContract\Model\OutgoingMessageStatus;
use App\Core\TransportContract\Model\Priority;
use App\Core\TransportContract\Model\SystemTransportConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[CoversClass(IntelPageTransport::class)]
#[CoversClass(IntelPageMessageTooLong::class)]
#[Group('unit')]
final class IntelPageTransportTest extends TestCase
{
    private function initTransport(?QueryBus $queryBus = null, ?CommandBus $commandBus = null, ?EventBus $eventBus = null): IntelPageTransport
    {
        $systemTransportConfigMock = self::createStub(SystemTransportConfig::class);
        $systemTransportConfigMock->method('getKey')->willReturn('custom-key');
        $queryBusMock = $queryBus ?? self::createStub(QueryBus::class);
        $commandBusMock = $commandBus ?? self::createStub(CommandBus::class);
        $eventBusMock = $eventBus ?? self::createStub(EventBus::class);

        return new IntelPageTransport(
            $systemTransportConfigMock,
            $queryBusMock,
            $commandBusMock,
            $eventBusMock,
        );
    }

    public function testKeyIsReturnedAsConfigured(): void
    {
        $transport = self::initTransport();
        self::assertSame('custom-key', $transport->key());
    }

    public function testAlwaysAcceptsNewMessages(): void
    {
        $transport = self::initTransport();
        self::assertTrue($transport->acceptsNewMessages());
    }

    public function testCanSendToReturnsFalseIfTransportWasNotConfigured(): void
    {
        $transport = self::initTransport();

        $messageRecipientMock = self::createStub(MessageRecipient::class);
        $messageRecipientMock->method('getTransportConfigurationFor')->with($transport)->willReturn(null);

        $messageMock = new class implements Message {
            public Ulid $messageId {
                get {
                    return Ulid::fromString('01JT62N5PE9HBQTEZ1PPE6CJ4F');
                }
            }
            public string $body = 'A';
            public Priority $priority = Priority::DEFAULT;
        };

        self::assertFalse($transport->canSendTo($messageRecipientMock, $messageMock));
    }

    public function testCanSendToWorksForChannels(): void
    {
        // Arrange
        $queryBus = self::createStub(QueryBus::class);
        $queryBus->method('get')->with(self::logicalAnd(self::isInstanceOf(ChannelCapCodeById::class), self::callback(fn (ChannelCapCodeById $value) => '01JT62N5PE9HBQTEZ1PPE6CJ4F' === $value->channelId)))->willReturn(CapCode::fromInt(222));

        $transport = self::initTransport($queryBus);

        $messageRecipientMock = self::createStub(MessageRecipient::class);
        $messageRecipientMock->method('getTransportConfigurationFor')->with($transport)->willReturn(['channel' => '01JT62N5PE9HBQTEZ1PPE6CJ4F']);

        $messageMock = new class implements Message {
            public Ulid $messageId {
                get {
                    return Ulid::fromString('01JT62N5PE9HBQTEZ1PPE6CJ4F');
                }
            }
            public string $body = 'qgpb2PoBt9hr6ZfUlMdvtnAibZHLvNZ0cHqqpRsuQu1n8agI50or83aq12ypFjfi360kwn3yq0YnSisYrTOwVyKxymJ4spvASR4nWvnjw5QeltBcA0eUbSlUv1ugkMPEMwvP8FTeuQXmoSWU2lOwN2BOTmiCGHtmOFUt5g6lGNxmblmpPEuHpqLVjTq5dlhv0B1lF1cokuLIaNpMQ2rr7KS6042SgQdT76BvevMLXwaMLAaKGOK2wz8xEVsLdEoV5GC1RTCrO8otd0Q8Srznlj1LLnFraRGrHPJNN5Va1eI8yOjR6FIJ0M1wnlqmXiRjDNbtcb2Q2WEKqe3kddkImoNSpbgHcRsVZZu2jZNVv2zqS1pbzGDIAjo1gMVm9KqyyonSNDuKPcvZpivSumYw4zZFnjg41JeMwVzlEscxqNDkNztRseCPUyEuJAClfe0LPn1Fz6nrQvVGPcjzMUhwKOAIOlRJtK03yptfr2G81GG3R9XbKy17PkQLJqk4BuNA';
            public Priority $priority = Priority::HIGH;
        };

        // Assert & Act
        self::assertTrue($transport->canSendTo($messageRecipientMock, $messageMock));
    }

    public function testCanSendToWorksForIndividuals(): void
    {
        // Arrange
        $pagerMock = self::createStub(Pager::class);
        $pagerMock->method('isActivated')->willReturn(true);
        $pagerMock->method('individualAlertCap')->willReturn(CapCode::fromInt(999));
        $pagerMock->method('individualNonAlertCap')->willReturn(CapCode::fromInt(111));

        $queryBus = self::createStub(QueryBus::class);
        $queryBus->method('get')->with(self::logicalAnd(self::isInstanceOf(PagerByRecipient::class), self::callback(fn (PagerByRecipient $value) => '01JT62N5PE9HBQTEZ1PPE6CJ4F' === $value->recipientId)))->willReturn($pagerMock);

        $transport = self::initTransport($queryBus);

        $messageRecipientMock = self::createStub(MessageRecipient::class);
        $messageRecipientMock->method('getTransportConfigurationFor')->with($transport)->willReturn([]);
        $messageRecipientMock->method('getId')->willReturn(Ulid::fromString('01JT62N5PE9HBQTEZ1PPE6CJ4F'));

        $messageMock = new class implements Message {
            public Ulid $messageId {
                get {
                    return Ulid::fromString('01JT62N5PE9HBQTEZ1PPE6CJ4F');
                }
            }
            public string $body = 'qgpb2PoBt9hr6ZfUlMdvtnAibZHLvNZ0cHqqpRsuQu1n8agI50or83aq12ypFjfi360kwn3yq0YnSisYrTOwVyKxymJ4spvASR4nWvnjw5QeltBcA0eUbSlUv1ugkMPEMwvP8FTeuQXmoSWU2lOwN2BOTmiCGHtmOFUt5g6lGNxmblmpPEuHpqLVjTq5dlhv0B1lF1cokuLIaNpMQ2rr7KS6042SgQdT76BvevMLXwaMLAaKGOK2wz8xEVsLdEoV5GC1RTCrO8otd0Q8Srznlj1LLnFraRGrHPJNN5Va1eI8yOjR6FIJ0M1wnlqmXiRjDNbtcb2Q2WEKqe3kddkImoNSpbgHcRsVZZu2jZNVv2zqS1pbzGDIAjo1gMVm9KqyyonSNDuKPcvZpivSumYw4zZFnjg41JeMwVzlEscxqNDkNztRseCPUyEuJAClfe0LPn1Fz6nrQvVGPcjzMUhwKOAIOlRJtK03yptfr2G81GG3R9XbKy17PkQLJqk4BuNA';
            public Priority $priority = Priority::HIGH;
        };

        // Assert & Act
        self::assertTrue($transport->canSendTo($messageRecipientMock, $messageMock));
    }

    public function testCanSendToFailsWhenMessageIsTooLong(): void
    {
        self::expectException(IntelPageMessageTooLong::class);
        self::expectExceptionMessage('The message was too long with 513 bytes, the maximum allowed is 512.');

        $messageRecipientMock = self::createStub(MessageRecipient::class);

        $messageMock = new class implements Message {
            public Ulid $messageId {
                get {
                    return Ulid::fromString('01JT62N5PE9HBQTEZ1PPE6CJ4F');
                }
            }
            public string $body = 'qgpb2PoBt9hr6ZfUlMdvtnAibZHLvNZ0cHqqpRsuQu1n8agI50or83aq12ypFjfiL360kwn3yq0YnSisYrTOwVyKxymJ4spvASR4nWvnjw5QeltBcA0eUbSlUv1ugkMPEMwvP8FTeuQXmoSWU2lOwN2BOTmiCGHtmOFUt5g6lGNxmblmpPEuHpqLVjTq5dlhv0B1lF1cokuLIaNpMQ2rr7KS6042SgQdT76BvevMLXwaMLAaKGOK2wz8xEVsLdEoV5GC1RTCrO8otd0Q8Srznlj1LLnFraRGrHPJNN5Va1eI8yOjR6FIJ0M1wnlqmXiRjDNbtcb2Q2WEKqe3kddkImoNSpbgHcRsVZZu2jZNVv2zqS1pbzGDIAjo1gMVm9KqyyonSNDuKPcvZpivSumYw4zZFnjg41JeMwVzlEscxqNDkNztRseCPUyEuJAClfe0LPn1Fz6nrQvVGPcjzMUhwKOAIOlRJtK03yptfr2G81GG3R9XbKy17PkQLJqk4BuNA';
            public Priority $priority = Priority::HIGH;
        };

        $transport = self::initTransport();
        $transport->canSendTo($messageRecipientMock, $messageMock);
    }

    public function testCanSendToPager(): void
    {
        // Expect
        $pagerMock = self::createStub(Pager::class);
        $pagerMock->method('isActivated')->willReturn(true);
        $pagerMock->method('individualAlertCap')->willReturn(CapCode::fromInt(999));
        $pagerMock->method('individualNonAlertCap')->willReturn(CapCode::fromInt(111));

        $queryBus = self::createStub(QueryBus::class);
        $queryBus->method('get')->with(self::logicalAnd(self::isInstanceOf(PagerByRecipient::class), self::callback(fn (PagerByRecipient $value) => '01JT62N5PE9HBQTEZ1PPE6CJ4F' === $value->recipientId)))->willReturn($pagerMock);

        $commandBus = self::createMock(CommandBus::class);
        $commandBus->expects(self::once())->method('do')->with(self::isInstanceOf(QueueMessage::class));

        // Arrange
        $transport = self::initTransport($queryBus, $commandBus);

        $messageRecipientMock = self::createStub(MessageRecipient::class);
        $messageRecipientMock->method('getTransportConfigurationFor')->with($transport)->willReturn([]);
        $messageRecipientMock->method('getId')->willReturn(Ulid::fromString('01JT62N5PE9HBQTEZ1PPE6CJ4F'));

        $messageMock = new class implements Message {
            public Ulid $messageId {
                get {
                    return Ulid::fromString('01JT62N5PE9HBQTEZ1PPE6CJ4F');
                }
            }
            public string $body = 'qgpb2PoBt9hr6ZfUlMdvtnAibZHLvNZ0cHqqpRsuQu1n8agI50or83aq12ypFjfi360kwn3yq0YnSisYrTOwVyKxymJ4spvASR4nWvnjw5QeltBcA0eUbSlUv1ugkMPEMwvP8FTeuQXmoSWU2lOwN2BOTmiCGHtmOFUt5g6lGNxmblmpPEuHpqLVjTq5dlhv0B1lF1cokuLIaNpMQ2rr7KS6042SgQdT76BvevMLXwaMLAaKGOK2wz8xEVsLdEoV5GC1RTCrO8otd0Q8Srznlj1LLnFraRGrHPJNN5Va1eI8yOjR6FIJ0M1wnlqmXiRjDNbtcb2Q2WEKqe3kddkImoNSpbgHcRsVZZu2jZNVv2zqS1pbzGDIAjo1gMVm9KqyyonSNDuKPcvZpivSumYw4zZFnjg41JeMwVzlEscxqNDkNztRseCPUyEuJAClfe0LPn1Fz6nrQvVGPcjzMUhwKOAIOlRJtK03yptfr2G81GG3R9XbKy17PkQLJqk4BuNA';
            public Priority $priority = Priority::DEFAULT;
        };

        $outgoingMessage = OutgoingMessage::for($messageRecipientMock, $messageMock);

        // Act
        $transport->send($outgoingMessage);
    }

    public function testCanSendToChannel(): void
    {
        // Expect
        $queryBus = self::createStub(QueryBus::class);
        $queryBus->method('get')->with(self::logicalAnd(self::isInstanceOf(ChannelCapCodeById::class), self::callback(fn (ChannelCapCodeById $value) => '01JT62N5PE9HBQTEZ1PPE6CJ4F' === $value->channelId)))->willReturn(CapCode::fromInt(222));

        $commandBus = self::createMock(CommandBus::class);
        $commandBus->expects(self::once())->method('do')->with(self::isInstanceOf(QueueMessage::class));

        // Arrange
        $transport = self::initTransport($queryBus, $commandBus);

        $messageRecipientMock = self::createStub(MessageRecipient::class);
        $messageRecipientMock->method('getTransportConfigurationFor')->with($transport)->willReturn(['channel' => '01JT62N5PE9HBQTEZ1PPE6CJ4F']);

        $messageMock = new class implements Message {
            public Ulid $messageId {
                get {
                    return Ulid::fromString('01JT62N5PE9HBQTEZ1PPE6CJ4F');
                }
            }
            public string $body = 'qgpb2PoBt9hr6ZfUlMdvtnAibZHLvNZ0cHqqpRsuQu1n8agI50or83aq12ypFjfi360kwn3yq0YnSisYrTOwVyKxymJ4spvASR4nWvnjw5QeltBcA0eUbSlUv1ugkMPEMwvP8FTeuQXmoSWU2lOwN2BOTmiCGHtmOFUt5g6lGNxmblmpPEuHpqLVjTq5dlhv0B1lF1cokuLIaNpMQ2rr7KS6042SgQdT76BvevMLXwaMLAaKGOK2wz8xEVsLdEoV5GC1RTCrO8otd0Q8Srznlj1LLnFraRGrHPJNN5Va1eI8yOjR6FIJ0M1wnlqmXiRjDNbtcb2Q2WEKqe3kddkImoNSpbgHcRsVZZu2jZNVv2zqS1pbzGDIAjo1gMVm9KqyyonSNDuKPcvZpivSumYw4zZFnjg41JeMwVzlEscxqNDkNztRseCPUyEuJAClfe0LPn1Fz6nrQvVGPcjzMUhwKOAIOlRJtK03yptfr2G81GG3R9XbKy17PkQLJqk4BuNA';
            public Priority $priority = Priority::DEFAULT;
        };

        $outgoingMessage = OutgoingMessage::for($messageRecipientMock, $messageMock);

        // Act
        $transport->send($outgoingMessage);
    }

    public function testSendCreaturesFailureEventWhenNoRecipientConfigIsMissing(): void
    { // Expect
        $eventBus = self::createMock(EventBus::class);
        $eventBus->expects(self::once())->method('publish')->with(self::logicalAnd(
            self::isInstanceOf(OutgoingMessageEvent::class),
            self::callback(fn (OutgoingMessageEvent $value) => OutgoingMessageStatus::ERROR === $value->status)
        ));

        // Arrange
        $transport = self::initTransport(eventBus: $eventBus);

        $messageRecipientMock = self::createStub(MessageRecipient::class);
        $messageRecipientMock->method('getTransportConfigurationFor')->with($transport)->willReturn(null);

        $messageMock = new class implements Message {
            public Ulid $messageId {
                get {
                    return Ulid::fromString('01JT62N5PE9HBQTEZ1PPE6CJ4F');
                }
            }
            public string $body = 't9hr6ZfUlMdvtnAibZHLvNZ0cHqqpRsuQu1n8agI50or83aq12ypFjfi360kwn3yq0YnSisYrTOwVyKxymJ4spvASR4nWvnjw5QeltBcA0eUbSlUv1ugkMPEMwvP8FTeuQXmoSWU2lOwN2BOTmiCGHtmOFUt5g6lGNxmblmpPEuHpqLVjTq5dlhv0B1lF1cokuLIaNpMQ2rr7KS6042SgQdT76BvevMLXwaMLAaKGOK2wz8xEVsLdEoV5GC1RTCrO8otd0Q8Srznlj1LLnFraRGrHPJNN5Va1eI8yOjR6FIJ0M1wnlqmXiRjDNbtcb2Q2WEKqe3kddkImoNSpbgHcRsVZZu2jZNVv2zqS1pbzGDIAjo1gMVm9KqyyonSNDuKPcvZpivSumYw4zZFnjg41JeMwVzlEscxqNDkNztRseCPUyEuJAClfe0LPn1Fz6nrQvVGPcjzMUhwKOAIOlRJtK03yptfr2G81GG3R9XbKy17PkQLJqk4BuNA';
            public Priority $priority = Priority::DEFAULT;
        };

        $outgoingMessage = OutgoingMessage::for($messageRecipientMock, $messageMock);

        // Act
        $transport->send($outgoingMessage);
    }
}
