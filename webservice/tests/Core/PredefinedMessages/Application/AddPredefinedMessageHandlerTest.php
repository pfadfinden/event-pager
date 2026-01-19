<?php

declare(strict_types=1);

namespace App\Tests\Core\PredefinedMessages\Application;

use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\PredefinedMessages\Application\AddPredefinedMessageHandler;
use App\Core\PredefinedMessages\Command\AddPredefinedMessage;
use App\Core\PredefinedMessages\Model\PredefinedMessage;
use App\Core\PredefinedMessages\Port\PredefinedMessageRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
#[CoversClass(AddPredefinedMessageHandler::class)]
#[CoversClass(AddPredefinedMessage::class)]
final class AddPredefinedMessageHandlerTest extends TestCase
{
    public function testAddPredefinedMessage(): void
    {
        $command = new AddPredefinedMessage(
            'Fire Alert',
            'Fire detected in building',
            5,
            ['01JNAY9HWQTEX1T45VBM2HG1XJ'],
            true,
            1,
            true,
        );

        $repo = $this->createMock(PredefinedMessageRepository::class);
        $repo->expects(self::once())
            ->method('add')
            ->with(self::callback(fn (PredefinedMessage $m): bool => 'Fire Alert' === $m->getTitle()
                && 'Fire detected in building' === $m->getMessageContent()
                && 5 === $m->getPriority()
                && ['01JNAY9HWQTEX1T45VBM2HG1XJ'] === $m->getRecipientIds()
                && true === $m->isFavorite()
                && 1 === $m->getSortOrder()
                && true === $m->isEnabled()));

        $uow = $this->createMock(UnitOfWork::class);
        $uow->expects(self::once())->method('commit');

        $sut = new AddPredefinedMessageHandler($repo, $uow);

        $sut($command);
    }

    public function testAddPredefinedMessageWithDefaults(): void
    {
        $command = new AddPredefinedMessage(
            'Test Message',
            'This is a test',
            3,
            [],
        );

        $repo = $this->createMock(PredefinedMessageRepository::class);
        $repo->expects(self::once())
            ->method('add')
            ->with(self::callback(fn (PredefinedMessage $m): bool => 'Test Message' === $m->getTitle()
                && false === $m->isFavorite()
                && 0 === $m->getSortOrder()
                && true === $m->isEnabled()));

        $uow = $this->createMock(UnitOfWork::class);
        $uow->expects(self::once())->method('commit');

        $sut = new AddPredefinedMessageHandler($repo, $uow);

        $sut($command);
    }
}
