<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\DoctrineORM\Query;

use App\Core\IntelPage\Model\CapCode;
use App\Core\IntelPage\Model\Channel;
use App\Core\IntelPage\Query\ChannelCapCodeById;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Symfony\Component\Uid\Ulid;
use function sprintf;

final readonly class ChannelCapCodeByIdQueryHandler
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function __invoke(ChannelCapCodeById $query): ?CapCode
    {
        $dql = sprintf('SELECT c.capCode.code FROM %s c WHERE c.id = :channelId', Channel::class);
        $parameters = ['channelId' => Ulid::fromString($query->channelId)->toRfc4122()];

        $doctrineQuery = $this->em->createQuery($dql);
        $doctrineQuery->setParameters($parameters);

        try {
            return CapCode::fromInt((int) $doctrineQuery->getSingleScalarResult());
        } catch (NoResultException) {
            return null;
        }
    }
}
