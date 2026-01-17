<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\DoctrineORM\Query;

use App\Core\IntelPage\Model\AbstractCapAssignment;
use App\Core\IntelPage\Model\ChannelCapAssignment;
use App\Core\IntelPage\Model\IndividualCapAssignment;
use App\Core\IntelPage\Model\NoCapAssignment;
use App\Core\IntelPage\Query\CapAssignments;
use App\Core\IntelPage\ReadModel\CapAssignment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Ulid;
use function sprintf;

final readonly class CapAssignmentsQueryHandler
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    /**
     * @return iterable<CapAssignment>
     */
    public function __invoke(CapAssignments $query): iterable
    {
        $dql = sprintf(
            "
                SELECT NEW %1s(
                    a.slot.slot,
                    CASE WHEN a INSTANCE OF %2\$s THEN c.name WHEN a INSTANCE OF %3\$s THEN 'Individual' ELSE 'Unknown' END AS type,
                    CASE WHEN a INSTANCE OF %2\$s THEN c.capCode.code WHEN a INSTANCE OF %3\$s THEN pi.capCode.code ELSE 0 END AS capCode,
                    CASE WHEN a INSTANCE OF %2\$s THEN c.audible WHEN a INSTANCE OF %3\$s THEN pi.audible ELSE false END AS audible,
                    CASE WHEN a INSTANCE OF %2\$s THEN c.vibration WHEN a INSTANCE OF %3\$s THEN pi.vibration ELSE false END AS vibration
                    )
                FROM %5\$s a
                LEFT JOIN %2\$s pc WITH a.id = pc.id
                LEFT JOIN %3\$s pi WITH a.id = pi.id
                LEFT JOIN pc.channel c
                WHERE a.pager = :pagerId AND NOT a INSTANCE OF %4\$s",
            CapAssignment::class,
            ChannelCapAssignment::class,
            IndividualCapAssignment::class,
            NoCapAssignment::class,
            AbstractCapAssignment::class
        );

        $doctrineQuery = $this->em->createQuery($dql);
        $doctrineQuery->setParameters(['pagerId' => Ulid::fromString($query->id)->toRfc4122()]);

        return $doctrineQuery->getResult();
    }
}
