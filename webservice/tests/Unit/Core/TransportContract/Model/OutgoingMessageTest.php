<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\TransportContract\Model;

use App\Core\TransportContract\Model\Message;
use App\Core\TransportContract\Model\MessageRecipient;
use App\Core\TransportContract\Model\OutgoingMessage;
use App\Core\TransportContract\Port\Transport;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(OutgoingMessage::class)]
final class OutgoingMessageTest extends TestCase
{
    public function testNew(): void
    {
        $recipient = self::createStub(MessageRecipient::class);
        $message = self::createStub(Message::class);
        $transport = self::createMock(Transport::class);
        $transport->expects(self::once())->method('key')->willReturn('default');

        $sut = OutgoingMessage::for($recipient, $message, $transport);

        self::assertSame('default', $sut->transport);
        self::assertEquals($message, $sut->incomingMessage);
        self::assertEquals($recipient, $sut->recipient);
    }

    public function testNewForFailure(): void
    {
        $recipient = self::createStub(MessageRecipient::class);
        $message = self::createStub(Message::class);

        $sut = OutgoingMessage::failure($recipient, $message);

        self::assertSame('_FAILED_', $sut->transport);
        self::assertEquals($message, $sut->incomingMessage);
        self::assertEquals($recipient, $sut->recipient);
    }
}
