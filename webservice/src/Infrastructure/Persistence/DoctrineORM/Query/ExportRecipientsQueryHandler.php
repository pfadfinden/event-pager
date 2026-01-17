<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\DoctrineORM\Query;

use App\Core\DataExchange\Query\ExportRecipients;
use App\Core\DataExchange\ReadModel\RecipientExportRow;
use App\Core\MessageRecipient\Model\AbstractMessageRecipient;
use App\Core\MessageRecipient\Model\Group;
use App\Core\MessageRecipient\Model\Person;
use App\Core\MessageRecipient\Model\RecipientTransportConfiguration;
use App\Core\MessageRecipient\Model\Role;
use Doctrine\ORM\EntityManagerInterface;
use function array_map;
use function implode;
use function json_encode;
use const JSON_THROW_ON_ERROR;

final readonly class ExportRecipientsQueryHandler
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    /**
     * @return iterable<RecipientExportRow>
     */
    public function __invoke(ExportRecipients $query): iterable
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('r')
            ->from(AbstractMessageRecipient::class, 'r')
            ->orderBy('r.name', 'ASC');

        if (null !== $query->filterType) {
            $targetClass = match ($query->filterType) {
                'PERSON' => Person::class,
                'GROUP' => Group::class,
                'ROLE' => Role::class,
                default => $query->filterType,
            };
            $qb->where('r INSTANCE OF :type')
                ->setParameter('type', $targetClass);
        }

        foreach ($qb->getQuery()->toIterable() as $recipient) {
            /** @var AbstractMessageRecipient $recipient */
            yield $this->mapToExportRow($recipient);
        }
    }

    private function mapToExportRow(AbstractMessageRecipient $recipient): RecipientExportRow
    {
        $type = match (true) {
            $recipient instanceof Person => 'PERSON',
            $recipient instanceof Group => 'GROUP',
            $recipient instanceof Role => 'ROLE',
            default => 'UNKNOWN',
        };

        $assignedPersonId = null;
        $groupMemberIds = null;

        if ($recipient instanceof Role && $recipient->person instanceof Person) {
            $assignedPersonId = $recipient->person->getId()->toRfc4122();
        }

        if ($recipient instanceof Group) {
            $members = $recipient->getMembers();
            if ([] !== $members) {
                $memberIds = array_map(
                    fn (AbstractMessageRecipient $m) => $m->getId()->toRfc4122(),
                    $members,
                );
                $groupMemberIds = implode(',', $memberIds);
            }
        }

        $transportConfigs = $this->encodeTransportConfigs($recipient->getTransportConfiguration());

        return new RecipientExportRow(
            $recipient->getId()->toRfc4122(),
            $type,
            $recipient->getName(),
            $assignedPersonId,
            $groupMemberIds,
            $transportConfigs,
        );
    }

    /**
     * @param array<string, RecipientTransportConfiguration> $configs
     */
    private function encodeTransportConfigs(array $configs): ?string
    {
        if ([] === $configs) {
            return null;
        }

        $data = [];
        foreach ($configs as $key => $config) {
            $data[$key] = [
                'enabled' => $config->isEnabled,
                'config' => $config->getVendorSpecificConfig(),
            ];
        }

        return json_encode($data, JSON_THROW_ON_ERROR);
    }
}
