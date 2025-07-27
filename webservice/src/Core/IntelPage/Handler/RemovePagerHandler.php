<?php

declare(strict_types=1);

namespace App\Core\IntelPage\Handler;

use App\Core\Contracts\Bus\Bus;
use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\IntelPage\Command\RemovePager;
use App\Core\IntelPage\Model\Pager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: Bus::COMMAND)]
final readonly class RemovePagerHandler
{
    public function __construct(
        private EntityManagerInterface $repository,
        private UnitOfWork $uow,
    ) {
    }

    public function __invoke(RemovePager $cmd): void
    {
        $pager = $this->repository->getRepository(Pager::class)->find($cmd->id);

        if (!$pager instanceof Pager) {
            return;
        }

        $this->repository->remove($pager);
        $this->uow->commit();
    }
}
