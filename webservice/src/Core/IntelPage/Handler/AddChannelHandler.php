<?php

declare(strict_types=1);

namespace App\Core\IntelPage\Handler;

use App\Core\Contracts\Bus\Bus;
use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\IntelPage\Command\AddChannel;
use App\Core\IntelPage\Model\CapCode;
use App\Core\IntelPage\Model\Channel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Uid\Ulid;

#[AsMessageHandler(bus: Bus::COMMAND)]
final readonly class AddChannelHandler
{
    public function __construct(
        private EntityManagerInterface $repository,
        private UnitOfWork $uow,
    ) {
    }

    public function __invoke(AddChannel $cmd): void
    {
        $pager = new Channel(Ulid::fromString($cmd->id), $cmd->name, CapCode::fromInt($cmd->capCode), $cmd->audible, $cmd->vibration);
        $this->repository->persist($pager);
        $this->uow->commit();
    }
}
