<?php

declare(strict_types=1);

namespace App\Tests\Core\TransportContract\Model;

use App\Core\TransportContract\Model\Message;
use App\Core\TransportContract\Model\MessageRecipient;
use App\Core\TransportContract\Model\OutgoingMessage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(OutgoingMessage::class)]
#[Group('unit')]
final class OutgoingMessageTest extends TestCase
{
    public function testFor(): void
    {
        $recipient = $this->createMock(MessageRecipient::class);
        $message = $this->createMock(Message::class);
        $sut = OutgoingMessage::for($recipient, $message);

        self::assertEquals($message, $sut->incomingMessage);
    }
}
