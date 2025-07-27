<?php

declare(strict_types=1);

namespace App\Tests\Core\IntelPage\Handler;

use App\Core\Contracts\Bus\EventBus;
use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\IntelPage\Command\QueueMessage;
use App\Core\IntelPage\Handler\QueueMessageHandler;
use App\Core\IntelPage\Model\CapCode;
use App\Core\IntelPage\Model\PagerMessage;
use App\Core\IntelPage\Port\PagerMessageRepository;
use App\Core\TransportContract\Model\OutgoingMessageEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[CoversClass(QueueMessageHandler::class), CoversClass(QueueMessage::class)]
#[Group('unit')]
final class QueueMessageHandlerTest extends TestCase
{
    public function testCanQueueMessage(): void
    {
        $id = Ulid::fromString(Ulid::generate());

        $pagerMessageRepositoryMock = self::createMock(PagerMessageRepository::class);
        $pagerMessageRepositoryMock->expects(self::once())->method('add')
            ->with(self::callback(fn (PagerMessage $m) => (
                'Hello World' === $m->getMessage()
                && $m->getId()->equals($id)
                && 1001 === $m->getCap()->getCode()
            )));
        // Pot. Improvement: Assert all properties if message are correct

        $unitOfWorkMock = self::createMock(UnitOfWork::class);
        $unitOfWorkMock->expects(self::once())->method('commit');

        $eventBusMock = self::createMock(EventBus::class);
        $eventBusMock->expects(self::once())->method('publish')->with(self::isInstanceOf(OutgoingMessageEvent::class));
        // Pot. Improvement: Assert properties of event are correct

        // sut = Subject Under Test i.e. the class we are testing
        $sut = new QueueMessageHandler($pagerMessageRepositoryMock, $unitOfWorkMock, $eventBusMock);

        $cmd = QueueMessage::with(
            $id,
            'default',
            CapCode::fromInt(1001),
            'Hello World',
            1,
            Ulid::fromString(Ulid::generate()),
        );

        // ACT
        $sut->__invoke($cmd);
    }
}
