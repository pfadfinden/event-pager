<?php

declare(strict_types=1);

namespace App\Tests\Core\IntelPage\Handler;

use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\IntelPage\Command\UpdateChannel;
use App\Core\IntelPage\Exception\ChannelNotFound;
use App\Core\IntelPage\Handler\UpdateChannelHandler;
use App\Core\IntelPage\Model\CapCode;
use App\Core\IntelPage\Model\Channel;
use App\Core\IntelPage\Port\ChannelRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[CoversClass(UpdateChannelHandler::class), CoversClass(UpdateChannel::class)]
#[Group('unit')]
final class UpdateChannelHandlerTest extends TestCase
{
    public function testCanUpdateChannel(): void
    {
        $id = '01JT62N5PE9HBQTEZ1PPE6CJ4F';
        $ulid = Ulid::fromString($id);

        $channelRepositoryMock = self::createMock(ChannelRepository::class);
        $channelRepositoryMock->expects(self::once())->method('getById')
            ->with(self::callback(fn (Ulid $ulidX) => $ulidX->equals($ulid)))
            ->willReturn(new Channel($ulid, 'Test', CapCode::fromInt(9999), true, true));
        $channelRepositoryMock->expects(self::once())->method('persist')
            ->with(self::callback(fn (Channel $channel) => (
                'Updated' === $channel->getName()
                && $channel->getId()->equals($ulid)
                && 1001 === $channel->getCapCode()->getCode()
                && false === $channel->isAudible()
                && false === $channel->isVibration()
            )));

        $unitOfWorkMock = self::createMock(UnitOfWork::class);
        $unitOfWorkMock->expects(self::once())->method('commit');

        // sut = Subject Under Test i.e. the class we are testing
        $sut = new UpdateChannelHandler($channelRepositoryMock, $unitOfWorkMock);

        $cmd = new UpdateChannel(
            $id,
            'Updated',
            1001,
            false,
            false,
        );

        // ACT
        $sut->__invoke($cmd);
    }

    public function testThrowsExceptionWhenChannelNotFound(): void
    {
        $id = '01JT62N5PE9HBQTEZ1PPE6CJ4F';
        $ulid = Ulid::fromString($id);

        $channelRepositoryMock = self::createMock(ChannelRepository::class);
        $channelRepositoryMock->expects(self::once())->method('getById')
            ->with(self::callback(fn (Ulid $ulidX) => $ulidX->equals($ulid)))
            ->willReturn(null);

        $unitOfWorkMock = self::createStub(UnitOfWork::class);

        $sut = new UpdateChannelHandler($channelRepositoryMock, $unitOfWorkMock);

        $cmd = new UpdateChannel($id, 'Updated', 1001, false, false);

        $this->expectException(ChannelNotFound::class);
        $this->expectExceptionMessage('Channel with id "01JT62N5PE9HBQTEZ1PPE6CJ4F" was not found.');

        $sut->__invoke($cmd);
    }
}
