<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\DoctrineORM\Query;

use App\Core\DataExchange\Query\ExportPagers;
use App\Core\DataExchange\ReadModel\PagerExportRow;
use App\Core\IntelPage\Model\AbstractCapAssignment;
use App\Core\IntelPage\Model\ChannelCapAssignment;
use App\Core\IntelPage\Model\IndividualCapAssignment;
use App\Core\IntelPage\Model\Pager;
use Doctrine\ORM\EntityManagerInterface;
use function json_encode;
use const JSON_THROW_ON_ERROR;

final readonly class ExportPagersQueryHandler
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    /**
     * @return iterable<PagerExportRow>
     */
    public function __invoke(ExportPagers $query): iterable
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('p')
            ->from(Pager::class, 'p')
            ->orderBy('p.number', 'ASC');

        if ($query->activeOnly) {
            $qb->where('p.activated = true');
        }

        foreach ($qb->getQuery()->toIterable() as $pager) {
            /** @var Pager $pager */
            yield new PagerExportRow(
                $pager->getId()->toRfc4122(),
                $pager->getLabel(),
                $pager->getNumber(),
                '' !== $pager->getComment() ? $pager->getComment() : null,
                $pager->isActivated(),
                $pager->getCarriedBy()?->getId()->toRfc4122(),
                $this->encodeSlotAssignments($pager->getCapAssignments()),
            );
        }
    }

    /**
     * @param iterable<AbstractCapAssignment> $assignments
     */
    private function encodeSlotAssignments(iterable $assignments): ?string
    {
        $data = [];
        foreach ($assignments as $assignment) {
            $slot = [
                'slot' => $assignment->getSlot()->getSlot(),
            ];

            if ($assignment instanceof IndividualCapAssignment) {
                $slot['type'] = 'individual';
                $slot['capCode'] = $assignment->getCapCode()->getCode();
                $slot['audible'] = $assignment->isAudible();
                $slot['vibration'] = $assignment->isVibration();
            } elseif ($assignment instanceof ChannelCapAssignment) {
                $slot['type'] = 'channel';
                $slot['channelId'] = $assignment->getChannel()->getId()->toRfc4122();
            }

            $data[] = $slot;
        }

        return [] !== $data ? json_encode($data, JSON_THROW_ON_ERROR) : null;
    }
}
