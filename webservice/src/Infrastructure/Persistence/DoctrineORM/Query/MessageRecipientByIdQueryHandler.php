<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\DoctrineORM\Query;

use App\Core\MessageRecipient\Model\AbstractMessageRecipient;
use App\Core\MessageRecipient\Model\Group;
use App\Core\MessageRecipient\Model\MessageRecipient;
use App\Core\MessageRecipient\Model\Person;
use App\Core\MessageRecipient\Model\Role;
use App\Core\MessageRecipient\Query\MessageRecipientById;
use App\Core\MessageRecipient\ReadModel\RecipientDetail;
use App\Core\MessageRecipient\ReadModel\RecipientListEntry;
use App\Core\MessageRecipient\ReadModel\TransportConfigurationEntry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Uid\Ulid;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class MessageRecipientByIdQueryHandler
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function __invoke(MessageRecipientById $query): ?RecipientDetail
    {
        if (!Ulid::isValid($query->id)) {
            return null;
        }

        $recipient = $this->em->getRepository(AbstractMessageRecipient::class)->find(Ulid::fromString($query->id));

        if (null === $recipient) {
            return null;
        }

        $type = $this->getType($recipient);
        $members = [];
        $groups = [];
        $assignedPerson = null;
        $assignedRoles = [];

        // Get groups this recipient belongs to
        foreach ($recipient->getGroups() as $group) {
            $groups[] = new RecipientListEntry(
                $group->getId()->toString(),
                'GROUP',
                $group->getName(),
            );
        }

        // Type-specific data
        if ($recipient instanceof Group) {
            foreach ($recipient->getMembers() as $member) {
                $members[] = new RecipientListEntry(
                    $member->getId()->toString(),
                    $this->getType($member),
                    $member->getName(),
                );
            }
        } elseif ($recipient instanceof Role && $recipient->canResolve()) {
            $person = $recipient->resolve();
            if ([] !== $person) {
                $assignedPerson = new RecipientListEntry(
                    $person[0]->getId()->toString(),
                    'PERSON',
                    $person[0]->getName(),
                );
            }
        } elseif ($recipient instanceof Person) {
            foreach ($recipient->getRoles() as $role) {
                $assignedRoles[] = new RecipientListEntry(
                    $role->getId()->toString(),
                    'ROLE',
                    $role->getName(),
                );
            }
        }

        // Get transport configurations (already sorted by rank descending in model)
        $transportConfigurations = [];
        foreach ($recipient->getTransportConfiguration() as $config) {
            $transportConfigurations[$config->getId()->toString()] = new TransportConfigurationEntry(
                $config->getId()->toString(),
                $config->getKey(),
                $config->isEnabled,
                $config->getVendorSpecificConfig(),
                $config->getRank(),
                $config->getSelectionExpression(),
                $config->getContinueInHierarchy(),
                $config->shouldEvaluateOtherTransportConfigurations(),
            );
        }

        return new RecipientDetail(
            $recipient->getId()->toString(),
            $type,
            $recipient->getName(),
            $members,
            $groups,
            $assignedPerson,
            $assignedRoles,
            $transportConfigurations,
        );
    }

    /**
     * @return "GROUP"|"ROLE"|"PERSON"
     */
    private function getType(MessageRecipient $recipient): string
    {
        return match (true) {
            $recipient instanceof Group => 'GROUP',
            $recipient instanceof Role => 'ROLE',
            $recipient instanceof Person => 'PERSON',
            default => 'PERSON',
        };
    }
}
