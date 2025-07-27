<?php

declare(strict_types=1);

namespace App\Core\IntelPage\Handler;

use App\Core\Contracts\Bus\Bus;
use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\IntelPage\Command\UpdateChannel;
use App\Core\IntelPage\Model\CapCode;
use App\Core\IntelPage\Model\Channel;
use App\Core\IntelPage\Port\ChannelRepository;
use RuntimeException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Uid\Ulid;

#[AsMessageHandler(bus: Bus::COMMAND)]
final readonly class UpdateChannelHandler
{
    public function __construct(
        private ChannelRepository $repository,
        private UnitOfWork $uow,
    ) {
    }

    public function __invoke(UpdateChannel $cmd): void
    {
        $channel = $this->repository->getById(Ulid::fromString($cmd->id));

        if (!$channel instanceof Channel) {
            throw new RuntimeException('Channel not found');
        }

        $channel->setName($cmd->name);
        $channel->setCapCode(CapCode::fromInt($cmd->capCode));
        $channel->setAudible($cmd->audible);
        $channel->setVibration($cmd->vibration);

        $this->repository->persist($channel);
        $this->uow->commit();
    }
}
