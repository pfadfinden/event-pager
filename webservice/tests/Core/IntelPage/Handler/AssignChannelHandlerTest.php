<?php

declare(strict_types=1);

namespace App\Tests\Core\IntelPage\Handler;

use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\IntelPage\Command\AssignChannel;
use App\Core\IntelPage\Exception\ChannelNotFound;
use App\Core\IntelPage\Exception\PagerNotFound;
use App\Core\IntelPage\Handler\AssignChannelHandler;
use App\Core\IntelPage\Model\CapCode;
use App\Core\IntelPage\Model\Channel;
use App\Core\IntelPage\Model\Pager;
use App\Core\IntelPage\Model\Slot;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[CoversClass(AssignChannelHandler::class), CoversClass(AssignChannel::class)]
#[Group('unit')]
final class AssignChannelHandlerTest extends TestCase
{
    public function testCanAssignChannel(): void
    {
        $pagerId = '01JT62N5PE9HBQTEZ1PPE6CJ4F';
        $channelId = '01JT62N5PE9HBQTEZ1PPE6CJ5G';
        $pagerUlid = Ulid::fromString($pagerId);
        $channelUlid = Ulid::fromString($channelId);
        $slot = 1;

        $pager = new Pager($pagerUlid, 'Test', 2);
        $channel = new Channel($channelUlid, 'Emergency', CapCode::fromInt(1234));

        $pagerRepositoryMock = self::createMock(EntityRepository::class);
        $pagerRepositoryMock->expects(self::once())->method('find')
            ->with($pagerId)
            ->willReturn($pager);

        $channelRepositoryMock = self::createMock(EntityRepository::class);
        $channelRepositoryMock->expects(self::once())->method('find')
            ->with($channelId)
            ->willReturn($channel);

        $entityManagerMock = self::createMock(EntityManagerInterface::class);
        $entityManagerMock->expects(self::exactly(2))->method('getRepository')
            ->willReturnCallback(function (string $class) use ($pagerRepositoryMock, $channelRepositoryMock) {
                return match ($class) {
                    Pager::class => $pagerRepositoryMock,
                    Channel::class => $channelRepositoryMock,
                    default => throw new InvalidArgumentException('Unexpected class: '.$class),
                };
            });
        $entityManagerMock->expects(self::once())->method('persist')
            ->with($pager);

        $unitOfWorkMock = self::createMock(UnitOfWork::class);
        $unitOfWorkMock->expects(self::once())->method('commit');

        // sut = Subject Under Test i.e. the class we are testing
        $sut = new AssignChannelHandler($entityManagerMock, $unitOfWorkMock);

        $cmd = new AssignChannel($pagerId, $slot, $channelId);

        // ACT
        $sut->__invoke($cmd);

        // ASSERT
        self::assertNotNull($pager->getCapAssignment(Slot::fromInt($slot)));
    }

    public function testThrowsExceptionWhenPagerNotFound(): void
    {
        $pagerId = '01JT62N5PE9HBQTEZ1PPE6CJ4F';
        $channelId = '01JT62N5PE9HBQTEZ1PPE6CJ5G';

        $pagerRepositoryMock = self::createMock(EntityRepository::class);
        $pagerRepositoryMock->expects(self::once())->method('find')
            ->with($pagerId)
            ->willReturn(null);

        $channelRepositoryMock = self::createMock(EntityRepository::class);
        $channelRepositoryMock->expects(self::once())->method('find')
            ->with($channelId)
            ->willReturn(null);

        $entityManagerMock = self::createMock(EntityManagerInterface::class);
        $entityManagerMock->expects(self::exactly(2))->method('getRepository')
            ->willReturnCallback(function (string $class) use ($pagerRepositoryMock, $channelRepositoryMock) {
                return match ($class) {
                    Pager::class => $pagerRepositoryMock,
                    Channel::class => $channelRepositoryMock,
                    default => throw new InvalidArgumentException('Unexpected class: '.$class),
                };
            });

        $unitOfWorkMock = self::createStub(UnitOfWork::class);

        $sut = new AssignChannelHandler($entityManagerMock, $unitOfWorkMock);

        $cmd = new AssignChannel($pagerId, 1, $channelId);

        $this->expectException(PagerNotFound::class);
        $this->expectExceptionMessage('Pager with id "01JT62N5PE9HBQTEZ1PPE6CJ4F" was not found.');

        $sut->__invoke($cmd);
    }

    public function testThrowsExceptionWhenChannelNotFound(): void
    {
        $pagerId = '01JT62N5PE9HBQTEZ1PPE6CJ4F';
        $channelId = '01JT62N5PE9HBQTEZ1PPE6CJ5G';
        $pagerUlid = Ulid::fromString($pagerId);

        $pager = new Pager($pagerUlid, 'Test', 2);

        $pagerRepositoryMock = self::createMock(EntityRepository::class);
        $pagerRepositoryMock->expects(self::once())->method('find')
            ->with($pagerId)
            ->willReturn($pager);

        $channelRepositoryMock = self::createMock(EntityRepository::class);
        $channelRepositoryMock->expects(self::once())->method('find')
            ->with($channelId)
            ->willReturn(null);

        $entityManagerMock = self::createMock(EntityManagerInterface::class);
        $entityManagerMock->expects(self::exactly(2))->method('getRepository')
            ->willReturnCallback(function (string $class) use ($pagerRepositoryMock, $channelRepositoryMock) {
                return match ($class) {
                    Pager::class => $pagerRepositoryMock,
                    Channel::class => $channelRepositoryMock,
                    default => throw new InvalidArgumentException('Unexpected class: '.$class),
                };
            });

        $unitOfWorkMock = self::createStub(UnitOfWork::class);

        $sut = new AssignChannelHandler($entityManagerMock, $unitOfWorkMock);

        $cmd = new AssignChannel($pagerId, 1, $channelId);

        $this->expectException(ChannelNotFound::class);
        $this->expectExceptionMessage('Channel with id "01JT62N5PE9HBQTEZ1PPE6CJ5G" was not found.');

        $sut->__invoke($cmd);
    }
}
