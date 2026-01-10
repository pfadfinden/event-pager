<?php

declare(strict_types=1);

namespace App\Core\IntelPage\Handler;

use App\Core\Contracts\Bus\Bus;
use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\IntelPage\Command\AssignIndividualCapCode;
use App\Core\IntelPage\Exception\PagerNotFound;
use App\Core\IntelPage\Model\CapCode;
use App\Core\IntelPage\Model\Pager;
use App\Core\IntelPage\Model\Slot;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: Bus::COMMAND)]
final readonly class AssignIndividualCapCodeHandler
{
    public function __construct(
        private EntityManagerInterface $repository,
        private UnitOfWork $uow,
    ) {
    }

    public function __invoke(AssignIndividualCapCode $cmd): void
    {
        $pager = $this->repository->getRepository(Pager::class)->find($cmd->pagerId);

        if (!$pager instanceof Pager) {
            throw PagerNotFound::withId($cmd->pagerId);
        }

        $pager->assignIndividualCap(Slot::fromInt($cmd->slot), CapCode::fromInt($cmd->capCode), $cmd->audible, $cmd->vibration);

        $this->repository->persist($pager);
        $this->uow->commit();
    }
}
