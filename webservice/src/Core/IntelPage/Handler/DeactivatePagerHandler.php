<?php

declare(strict_types=1);

namespace App\Core\IntelPage\Handler;

use App\Core\Contracts\Bus\Bus;
use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\IntelPage\Command\DeactivatePager;
use App\Core\IntelPage\Exception\PagerNotFound;
use App\Core\IntelPage\Model\Pager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: Bus::COMMAND)]
final readonly class DeactivatePagerHandler
{
    public function __construct(
        private EntityManagerInterface $repository,
        private UnitOfWork $uow,
    ) {
    }

    public function __invoke(DeactivatePager $cmd): void
    {
        $pager = $this->repository->getRepository(Pager::class)->find($cmd->id);

        if (!$pager instanceof Pager) {
            throw PagerNotFound::withId($cmd->id);
        }

        $pager->setActivated(false);

        $this->repository->persist($pager);
        $this->uow->commit();
    }
}
