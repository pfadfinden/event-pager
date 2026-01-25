<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\DoctrineORM\Query;

use App\Core\MessageRecipient\Model\AbstractMessageRecipient;
use App\Core\MessageRecipient\Model\Group;
use App\Core\MessageRecipient\Model\Person;
use App\Core\MessageRecipient\Model\RecipientTransportConfiguration;
use App\Core\MessageRecipient\Model\Role;
use App\Core\MessageRecipient\Query\ListOfMessageRecipients;
use App\Core\MessageRecipient\ReadModel\RecipientListEntry;
use App\Core\TransportManager\Model\TransportConfiguration;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Ulid;
use function in_array;
use function sprintf;

final readonly class ListOfMessageRecipientsQueryHandler
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    /**
     * @return iterable<RecipientListEntry>
     */
    public function __invoke(ListOfMessageRecipients $query): iterable
    {
        $dql = sprintf("SELECT NEW NAMED %s(r.id, (case
     when r INSTANCE OF %s then 'GROUP'
     when r INSTANCE OF %s then 'ROLE'
     when r INSTANCE OF %s then 'PERSON'
     else 'unknown'
   end) as type, r.name) FROM %s r", RecipientListEntry::class, Group::class, Role::class, Person::class, AbstractMessageRecipient::class);
        $parameters = [];
        $whereClauses = [];

        if (null !== $query->filterType) {
            $whereClauses[] = 'r INSTANCE OF :type';
            $parameters['type'] = $this->em->getClassMetadata($query->filterType);
        }

        if (null !== $query->textFilter && '' !== $query->textFilter) {
            $whereClauses[] = 'LOWER(r.name) LIKE LOWER(:textFilter)';
            $parameters['textFilter'] = '%'.$query->textFilter.'%';
        }

        if ([] !== $whereClauses) {
            $dql .= ' WHERE '.implode(' AND ', $whereClauses);
        }

        $dql .= ' ORDER BY r.name ASC';

        $doctrineQuery = $this->em->createQuery($dql);
        $doctrineQuery->setParameters($parameters);

        if (null !== $query->page && null !== $query->perPage) {
            $doctrineQuery->setFirstResult(($query->page - 1) * $query->perPage);
            $doctrineQuery->setMaxResults($query->perPage);
        } elseif (null !== $query->perPage) {
            $doctrineQuery->setMaxResults($query->perPage);
        }

        /** @var RecipientListEntry[] $recipients */
        $recipients = $doctrineQuery->getResult();

        if ([] === $recipients) {
            return $recipients;
        }

        // Fetch enabled transports for all recipients in a single query
        $recipientIds = array_values(array_map(static fn (RecipientListEntry $r): string => Ulid::fromString($r->id)->toRfc4122(), $recipients));
        $transportsByRecipient = $this->fetchEnabledTransports($recipientIds);

        // Merge enabled transports into each recipient
        foreach ($recipients as $recipient) {
            $recipient->enabledTransports = $transportsByRecipient[$recipient->id] ?? [];
        }

        return $recipients;
    }

    /**
     * Fetches enabled transport class names for a list of recipient IDs.
     *
     * @param list<string> $recipientIds
     *
     * @return array<string, list<string>> Map of recipient ID to list of short transport class names
     */
    private function fetchEnabledTransports(array $recipientIds): array
    {
        $dql = sprintf(
            'SELECT IDENTITY(rtc.recipient) as recipientId, t.transport as transportClass FROM %s rtc JOIN %s t WITH rtc.key = t.key WHERE IDENTITY(rtc.recipient) IN (:ids) AND rtc.isEnabled = true ORDER BY rtc.rank DESC',
            RecipientTransportConfiguration::class,
            TransportConfiguration::class,
        );

        $result = $this->em->createQuery($dql)
            ->setParameter('ids', $recipientIds)
            ->getResult();

        $transportsByRecipient = [];
        foreach ($result as $row) {
            // Convert RFC4122 UUID back to ULID format for consistent lookup
            /** @phpstan-ignore-next-line argument.type */
            $recipientId = Ulid::fromString($row['recipientId'])->toBase32();
            // Extract short class name from FQCN
            $transportClass = $this->getShortClassName($row['transportClass']);
            if (!isset($transportsByRecipient[$recipientId])) {
                $transportsByRecipient[$recipientId] = [];
            }
            // Avoid duplicates (multiple configs with same transport)
            if (!in_array($transportClass, $transportsByRecipient[$recipientId], true)) {
                $transportsByRecipient[$recipientId][] = $transportClass;
            }
        }

        return $transportsByRecipient;
    }

    /**
     * Extracts the short class name from a fully qualified class name.
     */
    private function getShortClassName(string $fqcn): string
    {
        $parts = explode('\\', $fqcn);

        return end($parts);
    }
}
