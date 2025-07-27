<?php

declare(strict_types=1);

namespace App\Core\IntelPage\Handler;

use App\Core\Contracts\Bus\Bus;
use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\IntelPage\Command\UpdatePager;
use App\Core\IntelPage\Model\Pager;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: Bus::COMMAND)]
final readonly class UpdatePagerHandler
{
    public function __construct(
        private EntityManagerInterface $repository,
        private UnitOfWork $uow,
    ) {
    }

    public function __invoke(UpdatePager $cmd): void
    {
        $pager = $this->repository->getRepository(Pager::class)->find($cmd->id);

        if (!$pager instanceof Pager) {
            throw new RuntimeException('Pager not found');
        }

        $pager->setLabel($cmd->label);
        $pager->setNumber($cmd->number);
        // TODO $pager->setCarriedBy(Ulid::fromString($cmd->carriedBy));

        $this->repository->persist($pager);
        $this->uow->commit();
    }
}
