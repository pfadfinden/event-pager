<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\IntelPage\Handler;

use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\IntelPage\Command\AddChannel;
use App\Core\IntelPage\Handler\AddChannelHandler;
use App\Core\IntelPage\Model\Channel;
use App\Core\IntelPage\Port\ChannelRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[CoversClass(AddChannelHandler::class), CoversClass(AddChannel::class)]
final class AddChannelHandlerTest extends TestCase
{
    public function testCanAddChannel(): void
    {
        $id = Ulid::generate();

        $channelRepositoryMock = self::createMock(ChannelRepository::class);
        $channelRepositoryMock->expects(self::once())->method('persist')
            ->with(self::callback(fn (Channel $channel): bool => (
                'default' === $channel->getName()
                && $channel->getId()->equals(Ulid::fromString($id))
                && 1001 === $channel->getCapCode()->getCode()
                && $channel->isAudible()
                && false === $channel->isVibration()
            )));
        // Pot. Improvement: Assert all properties of channel are correct

        $unitOfWorkMock = self::createMock(UnitOfWork::class);
        $unitOfWorkMock->expects(self::once())->method('commit');

        // sut = Subject Under Test i.e. the class we are testing
        $sut = new AddChannelHandler($channelRepositoryMock, $unitOfWorkMock);

        $cmd = new AddChannel(
            $id,
            'default',
            1001,
            true,
            false,
        );

        // ACT
        $sut->__invoke($cmd);
    }
}
