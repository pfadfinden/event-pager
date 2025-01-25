<?php

declare(strict_types=1);

namespace App\Tests\Core\IntelPage\Application;

use App\Core\Contracts\Bus\EventBus;
use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\IntelPage\Application\QueueMessageHandler;
use App\Core\IntelPage\Command\QueueMessage;
use App\Core\IntelPage\Model\CapCode;
use App\Core\IntelPage\Port\PagerMessageRepository;
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
        // TODO add expectations to the mocks, this should include that the ::add() method on the repository and the ::commit() method on the UnitOfWork is called
        // TODO expect that event was published on event bus
        $pagerMessageRepositoryMock = self::createMock(PagerMessageRepository::class);
        $unitOfWorkMock = self::createMock(UnitOfWork::class);
        $eventBusMock = self::createMock(EventBus::class);

        // sut = Subject Under Test i.e. the class we are testing
        $sut = new QueueMessageHandler($pagerMessageRepositoryMock, $unitOfWorkMock, $eventBusMock);

        $cmd = new QueueMessage(
            Ulid::fromString(Ulid::generate()),
            CapCode::fromInt(1001),
            'Hello World',
            1,
            Ulid::fromString(Ulid::generate()),
        );

        // ACT
        $sut->__invoke($cmd);
    }
}
