<?php

declare(strict_types=1);

namespace App\Core\IntelPage\Handler;

use App\Core\Contracts\Bus\Bus;
use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\IntelPage\Command\AddPager;
use App\Core\IntelPage\Model\Pager;
use App\Core\IntelPage\Port\PagerRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Uid\Ulid;

#[AsMessageHandler(bus: Bus::COMMAND)]
final readonly class AddPagerHandler
{
    public function __construct(
        private PagerRepository $repository,
        private UnitOfWork $uow,
    ) {
    }

    public function __invoke(AddPager $cmd): void
    {
        $pager = new Pager(Ulid::fromString($cmd->id), $cmd->label, $cmd->number);
        $this->repository->persist($pager);
        $this->uow->commit();
    }
}
