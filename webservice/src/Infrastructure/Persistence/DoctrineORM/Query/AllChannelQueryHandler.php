<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\DoctrineORM\Query;

use App\Core\IntelPage\Query\AllChannel;
use App\Core\IntelPage\ReadModel\Channel;
use Doctrine\ORM\EntityManagerInterface;
use function sprintf;

final readonly class AllChannelQueryHandler
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    /**
     * @return iterable<Channel>
     */
    public function __invoke(AllChannel $query): iterable
    {
        $dql = sprintf(
            'SELECT NEW %s(ch.id, ch.name, ch.capCode.code, ch.audible, ch.vibration) FROM %s ch',
            Channel::class,
            \App\Core\IntelPage\Model\Channel::class
        );
        $parameters = [];

        if (null !== $query->textFilter && '' !== $query->textFilter) {
            $dql .= ' WHERE ch.name LIKE :query';
            $parameters['query'] = '%'.$query->textFilter.'%';
        }

        $dql .= ' ORDER BY ch.capCode.code ASC';

        $doctrineQuery = $this->em->createQuery($dql);
        $doctrineQuery->setParameters($parameters);

        if ($query->page >= 1 || null !== $query->perPage) {
            $limit = $query->perPage ?? AllChannel::DEFAULT_PAGE_LENGTH;
            $doctrineQuery->setMaxResults($limit);
            $doctrineQuery->setFirstResult(max(((int) $query->page - 1) * $limit, 0));
        }

        return $doctrineQuery->toIterable();
    }
}
