<?php

declare(strict_types=1);

namespace App\Core\DataExchange\Handler;

use App\Core\Contracts\Bus\Bus;
use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\DataExchange\Command\ImportRecipients;
use App\Core\DataExchange\Exception\ImportValidationException;
use App\Core\DataExchange\Model\ImportConflictStrategy;
use App\Core\DataExchange\Model\ImportResult;
use App\Core\DataExchange\Port\FormatAdapterFactory;
use App\Core\DataExchange\ReadModel\RecipientExportRow;
use App\Core\MessageRecipient\Model\AbstractMessageRecipient;
use App\Core\MessageRecipient\Model\Group;
use App\Core\MessageRecipient\Model\Person;
use App\Core\MessageRecipient\Model\RecipientTransportConfiguration;
use App\Core\MessageRecipient\Model\Role;
use App\Core\MessageRecipient\Port\MessageRecipientRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Uid\Ulid;
use Throwable;
use function count;
use function explode;
use function is_array;
use function json_decode;
use function sprintf;
use function trim;
use const JSON_THROW_ON_ERROR;

#[AsMessageHandler(bus: Bus::COMMAND)]
final class ImportRecipientsHandler extends AbstractImportHandler
{
    /** @var array<int, array{id: string, type: string, members?: string, person?: string}> */
    private array $pendingRelationships = [];

    public function __construct(
        FormatAdapterFactory $formatFactory,
        private readonly MessageRecipientRepository $recipientRepository,
        UnitOfWork $uow,
    ) {
        parent::__construct($formatFactory, $uow);
    }

    /**
     * @return string[]
     */
    protected function getRequiredHeaders(): array
    {
        return RecipientExportRow::csvHeaders();
    }

    protected function getIdentifierField(): string
    {
        return 'id';
    }

    public function __invoke(ImportRecipients $command): ImportResult
    {
        $this->pendingRelationships = [];

        // First pass: Create all recipients without relationships
        $result = $this->executeImport(
            $command->filePath,
            $command->content,
            $command->format,
            $command->conflictStrategy,
            fn (array $row, int $index): string => $this->processRow($row, $command->conflictStrategy),
        );

        // Second pass: Apply relationships (group members, role assignments)
        // Note: $this->pendingRelationships is populated by processRow callbacks during executeImport
        $errors = $result->errors;
        /** @var array<int, array{id: string, type: string, members?: string, person?: string}> $pendingRelationships */
        $pendingRelationships = $this->pendingRelationships;
        foreach ($pendingRelationships as $relationship) {
            try {
                $this->applyRelationship($relationship);
            } catch (Throwable $e) {
                $errors[] = sprintf('Relationship error for %s: %s', $relationship['id'], $e->getMessage());
            }
        }

        $this->uow->commit();

        return new ImportResult($result->imported, $result->updated, count($result->skipped), $errors, $result->skipped);
    }

    /**
     * @param array<string, string> $row
     */
    private function processRow(array $row, ImportConflictStrategy $strategy): string
    {
        $id = Ulid::fromString($row['id']);
        $existing = $this->recipientRepository->getRecipientFromID($id);

        if ($existing instanceof AbstractMessageRecipient) {
            return match ($strategy) {
                ImportConflictStrategy::SKIP => 'skipped',
                ImportConflictStrategy::UPDATE => $this->updateRecipient($existing, $row),
                ImportConflictStrategy::ERROR => throw new ImportValidationException(sprintf('Recipient with ID %s already exists', $row['id'])),
            };
        }

        $this->createRecipient($row, $id);

        return 'imported';
    }

    /**
     * @param array<string, string> $row
     */
    private function createRecipient(array $row, Ulid $id): void
    {
        $recipient = match ($row['type']) {
            'PERSON' => new Person($row['name'], $id),
            'GROUP' => new Group($row['name'], $id),
            'ROLE' => new Role($row['name'], null, $id),
            default => throw new ImportValidationException(sprintf('Unknown recipient type: %s', $row['type'])),
        };

        $this->applyTransportConfigs($recipient, $row['transport_configs']);
        $this->recipientRepository->add($recipient);
        $this->queueRelationships($row);
    }

    /**
     * @param array<string, string> $row
     */
    private function updateRecipient(AbstractMessageRecipient $recipient, array $row): string
    {
        $recipient->setName($row['name']);
        $this->applyTransportConfigs($recipient, $row['transport_configs']);
        $this->queueRelationships($row);

        return 'updated';
    }

    /**
     * @param array<string, string> $row
     */
    private function queueRelationships(array $row): void
    {
        if ('GROUP' === $row['type'] && '' !== $row['group_member_ids']) {
            $this->pendingRelationships[] = [
                'id' => $row['id'],
                'type' => 'group_members',
                'members' => $row['group_member_ids'],
            ];
        }

        if ('ROLE' === $row['type'] && '' !== $row['assigned_person_id']) {
            $this->pendingRelationships[] = [
                'id' => $row['id'],
                'type' => 'role_person',
                'person' => $row['assigned_person_id'],
            ];
        }
    }

    private function applyTransportConfigs(AbstractMessageRecipient $recipient, string $configsJson): void
    {
        if ('' === $configsJson) {
            return;
        }

        /** @var array<string, array{key: string, rank?: int, enabled?: bool, config?: array<string, mixed>}>|null $configs */
        $configs = json_decode($configsJson, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($configs)) {
            return;
        }

        foreach ($configs as $configId => $configData) {
            $transportKey = $configData['key'];
            $id = Ulid::fromString($configId);

            // Find existing config by ID and update, or create a new one
            $config = $recipient->getTransportConfigurationById($id);
            if (!$config instanceof RecipientTransportConfiguration) {
                $config = $recipient->addTransportConfigurationWithId($transportKey, $id);
            }

            $config->isEnabled = $configData['enabled'] ?? false;
            if (isset($configData['rank'])) {
                $config->setRank($configData['rank']);
            }
            if (isset($configData['config'])) {
                $config->setVendorSpecificConfig($configData['config']);
            }
        }
    }

    /**
     * @param array{id: string, type: string, members?: string, person?: string} $relationship
     */
    private function applyRelationship(array $relationship): void
    {
        $recipient = $this->recipientRepository->getRecipientFromID(Ulid::fromString($relationship['id']));

        if (!$recipient instanceof AbstractMessageRecipient) {
            return;
        }

        if ('group_members' === $relationship['type'] && $recipient instanceof Group && isset($relationship['members'])) {
            $memberIds = explode(',', $relationship['members']);
            foreach ($memberIds as $memberId) {
                $memberId = trim($memberId);
                if ('' === $memberId) {
                    continue;
                }
                $member = $this->recipientRepository->getRecipientFromID(Ulid::fromString($memberId));
                if ($member instanceof AbstractMessageRecipient) {
                    $recipient->addMember($member);
                }
            }
        }

        if ('role_person' === $relationship['type'] && $recipient instanceof Role && isset($relationship['person'])) {
            $person = $this->recipientRepository->getRecipientFromID(Ulid::fromString($relationship['person']));
            if ($person instanceof Person) {
                $recipient->bindPerson($person);
            }
        }
    }
}
