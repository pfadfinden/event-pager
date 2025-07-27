<?php

declare(strict_types=1);

namespace App\Core\IntelPage\Handler;

use App\Core\Contracts\Bus\Bus;
use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\IntelPage\Command\AssignChannel;
use App\Core\IntelPage\Model\Channel;
use App\Core\IntelPage\Model\Pager;
use App\Core\IntelPage\Model\Slot;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: Bus::COMMAND)]
final readonly class AssignChannelHandler
{
    public function __construct(
        private EntityManagerInterface $repository,
        private UnitOfWork $uow,
    ) {
    }

    public function __invoke(AssignChannel $cmd): void
    {
        $pager = $this->repository->getRepository(Pager::class)->find($cmd->pagerId);
        $channel = $this->repository->getRepository(Channel::class)->find($cmd->channelId);

        if (!$pager instanceof Pager) {
            throw new RuntimeException('Pager not found');
        }

        if (!$channel instanceof Channel) {
            throw new RuntimeException('Channel not found');
        }

        $pager->assignChannel(Slot::fromInt($cmd->slot), $channel);

        $this->repository->persist($pager);
        $this->uow->commit();
    }
}
