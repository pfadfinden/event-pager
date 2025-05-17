<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\DoctrineORM\Query;

use App\Core\IntelPage\Model\Pager;
use App\Core\IntelPage\Query\PagerByRecipient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Ulid;
use function sprintf;

final readonly class PagerByRecipientQueryHandler
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function __invoke(PagerByRecipient $query): ?Pager
    {
        $dql = sprintf('SELECT p FROM %s p JOIN p.carriedBy c WHERE c.id = :recipientId', Pager::class);
        $parameters = ['recipientId' => Ulid::fromString($query->recipientId)->toRfc4122()];

        $doctrineQuery = $this->em->createQuery($dql);
        $doctrineQuery->setParameters($parameters);

        // @phpstan-ignore return.type
        return $doctrineQuery->getOneOrNullResult();
    }
}
