<?php

declare(strict_types=1);

namespace App\Core\IntelPage\Handler;

use App\Core\Contracts\Bus\Bus;
use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\IntelPage\Command\RemoveChannel;
use App\Core\IntelPage\Model\Channel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: Bus::COMMAND)]
final readonly class RemoveChannelHandler
{
    public function __construct(
        private EntityManagerInterface $repository,
        private UnitOfWork $uow,
    ) {
    }

    public function __invoke(RemoveChannel $cmd): void
    {
        $channel = $this->repository->getRepository(Channel::class)->find($cmd->id);

        if (!$channel instanceof Channel) {
            return;
        }

        $this->repository->remove($channel);
        $this->uow->commit();
    }
}
