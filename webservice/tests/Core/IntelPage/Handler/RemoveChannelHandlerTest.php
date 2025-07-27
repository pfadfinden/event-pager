<?php

declare(strict_types=1);

namespace App\Tests\Core\IntelPage\Handler;

use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\IntelPage\Command\RemoveChannel;
use App\Core\IntelPage\Handler\RemoveChannelHandler;
use App\Core\IntelPage\Model\CapCode;
use App\Core\IntelPage\Model\Channel;
use App\Core\IntelPage\Port\ChannelRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[CoversClass(RemoveChannelHandler::class), CoversClass(RemoveChannel::class)]
#[Group('unit')]
final class RemoveChannelHandlerTest extends TestCase
{
    public function testCanRemoveChannel(): void
    {
        $id = '01JT62N5PE9HBQTEZ1PPE6CJ4F';
        $ulid = Ulid::fromString($id);
        $channel = new Channel($ulid, 'Test', CapCode::fromInt(9999), true, true);

        $channelRepositoryMock = self::createMock(ChannelRepository::class);

        $channelRepositoryMock->expects(self::once())->method('getById')
            ->with(self::callback(fn (Ulid $ulidX) => $ulidX->equals($ulid)))
            ->willReturn($channel);
        $channelRepositoryMock->expects(self::once())->method('remove')
            ->with($channel);

        $unitOfWorkMock = self::createMock(UnitOfWork::class);
        $unitOfWorkMock->expects(self::once())->method('commit');

        // sut = Subject Under Test i.e. the class we are testing
        $sut = new RemoveChannelHandler($channelRepositoryMock, $unitOfWorkMock);

        $cmd = new RemoveChannel($id);

        // ACT
        $sut->__invoke($cmd);
    }
}
