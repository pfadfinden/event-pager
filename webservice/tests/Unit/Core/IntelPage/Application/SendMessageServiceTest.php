<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\IntelPage\Application;

use App\Core\IntelPage\Application\SendPagerMessageService;
use App\Core\IntelPage\Exception\IntelPageTransmitterNotAvailable;
use App\Core\IntelPage\Model\CapCode;
use App\Core\IntelPage\Model\PagerMessage;
use App\Core\IntelPage\Port\IntelPageTransmitterInterface;
use App\Core\TransportContract\Model\OutgoingMessageEvent;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

#[CoversClass(SendPagerMessageService::class)]
final class SendMessageServiceTest extends TestCase
{
    public function testSendMessage(): void
    {
        // Arrange
        $message = $this->createMock(PagerMessage::class);
        $message->method('getCap')->willReturn(CapCode::fromString('777'));
        $message->method('getMessage')->willReturn('Hello World');
        $message->expects(self::once())->method('markSend');
        $message->expects(self::never())->method('failedToSend');

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('persist')->with($message);
        $em->expects(self::once())->method('flush');

        $transmitter = $this->createMock(IntelPageTransmitterInterface::class);
        $transmitter->expects(self::once())->method('transmit')->with($message->getCap(), $message->getMessage());

        $eventBus = $this->createMock(MessageBusInterface::class);
        $eventBus->expects(self::once())->method('dispatch')->with(Assert::isInstanceOf(OutgoingMessageEvent::class))->willReturnCallback(fn (object $m): Envelope => Envelope::wrap($m));

        $sut = new SendPagerMessageService($em, $transmitter, $eventBus);

        // Act
        $sut->send($message);

        // Assert -> see expects
    }

    public function testSendMessageHandlesTransmitterFailures(): void
    {
        // Arrange

        $message = $this->createMock(PagerMessage::class);
        $message->method('getCap')->willReturn(CapCode::fromString('777'));
        $message->method('getMessage')->willReturn('Hello World');
        $message->expects(self::never())->method('markSend');
        $message->expects(self::exactly(2))->method('failedToSend');
        $message->expects(self::exactly(2))->method('getAttemptedToSend')->willReturnOnConsecutiveCalls(1, 2);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::exactly(2))->method('persist')->with($message);
        $em->expects(self::exactly(2))->method('flush');

        $transmitter = $this->createMock(IntelPageTransmitterInterface::class);
        $transmitter->expects(self::exactly(2))->method('transmit')
            ->with($message->getCap(), $message->getMessage())
            ->willThrowException(new IntelPageTransmitterNotAvailable('test'));

        $eventBus = $this->createMock(MessageBusInterface::class);
        $eventBus->expects(self::once())->method('dispatch')->with(Assert::isInstanceOf(OutgoingMessageEvent::class))->willReturnCallback(fn (object $m): Envelope => Envelope::wrap($m));

        $sut = new SendPagerMessageService($em, $transmitter, $eventBus);

        // Act
        try {
            $sut->send($message);
            self::fail('Expected exception not thrown');
        } catch (IntelPageTransmitterNotAvailable) {
            // expected
        }

        // call a second time to reach retry limit and assert event emitted
        $this->expectException(IntelPageTransmitterNotAvailable::class);
        $sut->send($message);

        // Assert -> see expects
    }
}
