<?php

declare(strict_types=1);

namespace App\Tests\Core\IntelPage\Application;

use App\Core\Contracts\Bus\CommandBus;
use App\Core\Contracts\Bus\EventBus;
use App\Core\Contracts\Bus\QueryBus;
use App\Core\IntelPage\Application\IntelPageTransport;
use App\Core\IntelPage\Exception\IntelPageMessageTooLong;
use App\Core\IntelPage\Model\Pager;
use App\Core\TransportContract\Model\Message;
use App\Core\TransportContract\Model\MessageRecipient;
use App\Core\TransportContract\Model\SystemTransportConfig;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
final class IntelPageTransportTest extends TestCase
{
    private function initTransport(): IntelPageTransport
    {
        $systemTransportConfigMock = self::createMock(SystemTransportConfig::class);
        $queryBusMock = self::createMock(QueryBus::class);
        $commandBusMock = self::createMock(CommandBus::class);
        $eventBusMock = self::createMock(EventBus::class);

        $pagerMock = self::createMock(Pager::class);

        return new IntelPageTransport(
            $systemTransportConfigMock,
            $queryBusMock,
            $commandBusMock,
            $eventBusMock,
        );
    }

    public function testCanSendToFailsWhenMessageIsTooLong(): void
    {
        self::expectException(IntelPageMessageTooLong::class);
        self::expectExceptionMessage('The message was too long with 513 Bytes, the maximum allowed is 512.');

        $messageRecipientMock = self::createMock(MessageRecipient::class);
        $MessageMock = self::createMock(Message::class);
        $transport = self::initTransport();
        // TODO: update Mocks and create Message that is 513 Bytes long
        $transport->canSendTo($messageRecipientMock, $MessageMock);
    }
}
