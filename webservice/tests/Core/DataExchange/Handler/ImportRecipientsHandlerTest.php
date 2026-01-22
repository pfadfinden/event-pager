<?php

declare(strict_types=1);

namespace App\Tests\Core\DataExchange\Handler;

use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\DataExchange\Command\ImportRecipients;
use App\Core\DataExchange\Handler\ImportRecipientsHandler;
use App\Core\DataExchange\Model\ExportFormat;
use App\Core\DataExchange\Model\ImportConflictStrategy;
use App\Core\DataExchange\Port\FormatAdapterFactory;
use App\Core\DataExchange\Port\ImportParser;
use App\Core\MessageRecipient\Model\AbstractMessageRecipient;
use App\Core\MessageRecipient\Model\Group;
use App\Core\MessageRecipient\Model\Person;
use App\Core\MessageRecipient\Model\Role;
use App\Core\MessageRecipient\Port\MessageRecipientRepository;
use ArrayIterator;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group as TestGroup;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[CoversClass(ImportRecipientsHandler::class)]
#[CoversClass(ImportRecipients::class)]
#[TestGroup('unit')]
#[AllowMockObjectsWithoutExpectations]
final class ImportRecipientsHandlerTest extends TestCase
{
    private MessageRecipientRepository&MockObject $recipientRepository;
    private UnitOfWork&MockObject $uow;
    private ImportParser&MockObject $parser;
    private ImportRecipientsHandler $handler;

    protected function setUp(): void
    {
        $formatFactory = $this->createMock(FormatAdapterFactory::class);
        $this->recipientRepository = $this->createMock(MessageRecipientRepository::class);
        $this->uow = $this->createMock(UnitOfWork::class);
        $this->parser = $this->createMock(ImportParser::class);

        $formatFactory
            ->method('createParser')
            ->willReturn($this->parser);

        $this->handler = new ImportRecipientsHandler(
            $formatFactory,
            $this->recipientRepository,
            $this->uow,
        );
    }

    public function testImportNewPersonRecipient(): void
    {
        $id = Ulid::generate();

        $rows = [
            [
                'id' => $id,
                'type' => 'PERSON',
                'name' => 'John Doe',
                'assigned_person_id' => '',
                'group_member_ids' => '',
                'transport_configs' => '',
            ],
        ];

        $this->parser
            ->method('parse')
            ->willReturn(new ArrayIterator($rows));

        $this->recipientRepository
            ->method('getRecipientFromID')
            ->willReturn(null);

        $this->recipientRepository
            ->expects(self::once())
            ->method('add')
            ->with(self::isInstanceOf(Person::class));

        $this->uow
            ->expects(self::exactly(2))
            ->method('commit');

        $command = ImportRecipients::fromContent('', ExportFormat::CSV, ImportConflictStrategy::SKIP);

        $result = ($this->handler)($command);

        self::assertSame(1, $result->imported);
        self::assertSame(0, $result->updated);
        self::assertSame(0, $result->skippedCount);
        self::assertEmpty($result->errors);
    }

    public function testImportNewGroupRecipient(): void
    {
        $id = Ulid::generate();

        $rows = [
            [
                'id' => $id,
                'type' => 'GROUP',
                'name' => 'Security Team',
                'assigned_person_id' => '',
                'group_member_ids' => '',
                'transport_configs' => '',
            ],
        ];

        $this->parser
            ->method('parse')
            ->willReturn(new ArrayIterator($rows));

        $this->recipientRepository
            ->method('getRecipientFromID')
            ->willReturn(null);

        $addedRecipient = null;
        $this->recipientRepository
            ->method('add')
            ->willReturnCallback(function (AbstractMessageRecipient $recipient) use (&$addedRecipient): void {
                $addedRecipient = $recipient;
            });

        $command = ImportRecipients::fromContent('', ExportFormat::CSV, ImportConflictStrategy::SKIP);

        $result = ($this->handler)($command);

        self::assertSame(1, $result->imported);
        self::assertInstanceOf(Group::class, $addedRecipient);
        self::assertSame('Security Team', $addedRecipient->getName());
    }

    public function testImportNewRoleRecipient(): void
    {
        $id = Ulid::generate();

        $rows = [
            [
                'id' => $id,
                'type' => 'ROLE',
                'name' => 'Team Lead',
                'assigned_person_id' => '',
                'group_member_ids' => '',
                'transport_configs' => '',
            ],
        ];

        $this->parser
            ->method('parse')
            ->willReturn(new ArrayIterator($rows));

        $this->recipientRepository
            ->method('getRecipientFromID')
            ->willReturn(null);

        $addedRecipient = null;
        $this->recipientRepository
            ->method('add')
            ->willReturnCallback(function (AbstractMessageRecipient $recipient) use (&$addedRecipient): void {
                $addedRecipient = $recipient;
            });

        $command = ImportRecipients::fromContent('', ExportFormat::CSV, ImportConflictStrategy::SKIP);

        $result = ($this->handler)($command);

        self::assertSame(1, $result->imported);
        self::assertInstanceOf(Role::class, $addedRecipient);
        self::assertSame('Team Lead', $addedRecipient->getName());
    }

    public function testImportWithSkipStrategySkipsExisting(): void
    {
        $id = Ulid::generate();
        $existingPerson = new Person('Existing Person', Ulid::fromString($id));

        $rows = [
            [
                'id' => $id,
                'type' => 'PERSON',
                'name' => 'Updated Name',
                'assigned_person_id' => '',
                'group_member_ids' => '',
                'transport_configs' => '',
            ],
        ];

        $this->parser
            ->method('parse')
            ->willReturn(new ArrayIterator($rows));

        $this->recipientRepository
            ->method('getRecipientFromID')
            ->willReturn($existingPerson);

        $this->recipientRepository
            ->expects(self::never())
            ->method('add');

        $command = ImportRecipients::fromContent('', ExportFormat::CSV, ImportConflictStrategy::SKIP);

        $result = ($this->handler)($command);

        self::assertSame(0, $result->imported);
        self::assertSame(0, $result->updated);
        self::assertSame(1, $result->skippedCount);
    }

    public function testImportWithUpdateStrategyUpdatesExisting(): void
    {
        $id = Ulid::generate();
        $existingPerson = new Person('Old Name', Ulid::fromString($id));

        $rows = [
            [
                'id' => $id,
                'type' => 'PERSON',
                'name' => 'New Name',
                'assigned_person_id' => '',
                'group_member_ids' => '',
                'transport_configs' => '',
            ],
        ];

        $this->parser
            ->method('parse')
            ->willReturn(new ArrayIterator($rows));

        $this->recipientRepository
            ->method('getRecipientFromID')
            ->willReturn($existingPerson);

        $command = ImportRecipients::fromContent('', ExportFormat::CSV, ImportConflictStrategy::UPDATE);

        $result = ($this->handler)($command);

        self::assertSame(0, $result->imported);
        self::assertSame(1, $result->updated);
        self::assertSame(0, $result->skippedCount);
        self::assertSame('New Name', $existingPerson->getName());
    }

    public function testImportWithErrorStrategyReportsErrorOnExisting(): void
    {
        $id = Ulid::generate();
        $existingPerson = new Person('Existing Person', Ulid::fromString($id));

        $rows = [
            [
                'id' => $id,
                'type' => 'PERSON',
                'name' => 'Duplicate',
                'assigned_person_id' => '',
                'group_member_ids' => '',
                'transport_configs' => '',
            ],
        ];

        $this->parser
            ->method('parse')
            ->willReturn(new ArrayIterator($rows));

        $this->recipientRepository
            ->method('getRecipientFromID')
            ->willReturn($existingPerson);

        $command = ImportRecipients::fromContent('', ExportFormat::CSV, ImportConflictStrategy::ERROR);

        $result = ($this->handler)($command);

        self::assertSame(0, $result->imported);
        self::assertSame(0, $result->updated);
        self::assertCount(1, $result->errors);
        self::assertStringContainsString('already exists', $result->errors[0]);
    }

    public function testImportWithMissingColumnsReportsError(): void
    {
        $rows = [
            ['id' => Ulid::generate(), 'type' => 'PERSON', 'name' => 'Missing columns'],
            // Missing assigned_person_id, group_member_ids, transport_configs
        ];

        $this->parser
            ->method('parse')
            ->willReturn(new ArrayIterator($rows));

        $command = ImportRecipients::fromContent('', ExportFormat::CSV, ImportConflictStrategy::SKIP);

        $result = ($this->handler)($command);

        self::assertSame(0, $result->imported);
        self::assertCount(1, $result->errors);
        self::assertStringContainsString('Missing required columns', $result->errors[0]);
    }

    public function testImportWithUnknownTypeReportsError(): void
    {
        $id = Ulid::generate();

        $rows = [
            [
                'id' => $id,
                'type' => 'UNKNOWN',
                'name' => 'Unknown Type',
                'assigned_person_id' => '',
                'group_member_ids' => '',
                'transport_configs' => '',
            ],
        ];

        $this->parser
            ->method('parse')
            ->willReturn(new ArrayIterator($rows));

        $this->recipientRepository
            ->method('getRecipientFromID')
            ->willReturn(null);

        $command = ImportRecipients::fromContent('', ExportFormat::CSV, ImportConflictStrategy::SKIP);

        $result = ($this->handler)($command);

        self::assertSame(0, $result->imported);
        self::assertCount(1, $result->errors);
        self::assertStringContainsString('Unknown recipient type', $result->errors[0]);
    }

    public function testImportFromFileUsesParseFile(): void
    {
        $id = Ulid::generate();
        $rows = [
            [
                'id' => $id,
                'type' => 'PERSON',
                'name' => 'File Person',
                'assigned_person_id' => '',
                'group_member_ids' => '',
                'transport_configs' => '',
            ],
        ];

        $this->parser
            ->expects(self::once())
            ->method('parseFile')
            ->with('/path/to/file.csv')
            ->willReturn(new ArrayIterator($rows));

        $this->recipientRepository
            ->method('getRecipientFromID')
            ->willReturn(null);

        $command = ImportRecipients::fromFile('/path/to/file.csv', ExportFormat::CSV, ImportConflictStrategy::SKIP);

        $result = ($this->handler)($command);

        self::assertSame(1, $result->imported);
    }

    public function testImportGroupWithMembersAppliesRelationships(): void
    {
        $groupId = Ulid::generate();
        $memberId = Ulid::generate();

        $rows = [
            [
                'id' => $groupId,
                'type' => 'GROUP',
                'name' => 'Team Group',
                'assigned_person_id' => '',
                'group_member_ids' => $memberId,
                'transport_configs' => '',
            ],
        ];

        $this->parser
            ->method('parse')
            ->willReturn(new ArrayIterator($rows));

        $memberPerson = new Person('Member Person', Ulid::fromString($memberId));
        $addedGroup = null;

        $this->recipientRepository
            ->method('add')
            ->willReturnCallback(function (AbstractMessageRecipient $recipient) use (&$addedGroup): void {
                if ($recipient instanceof Group) {
                    $addedGroup = $recipient;
                }
            });

        $this->recipientRepository
            ->method('getRecipientFromID')
            ->willReturnCallback(function (Ulid $id) use ($groupId, $memberId, $memberPerson, &$addedGroup): ?AbstractMessageRecipient {
                if ($id->toString() === $memberId) {
                    return $memberPerson;
                }
                if ($id->toString() === $groupId && $addedGroup instanceof Group) {
                    return $addedGroup;
                }

                return null;
            });
        $command = ImportRecipients::fromContent('', ExportFormat::CSV, ImportConflictStrategy::SKIP);

        $result = ($this->handler)($command);

        self::assertSame(1, $result->imported);
        self::assertInstanceOf(Group::class, $addedGroup);
        self::assertCount(1, $addedGroup->getMembers());
        self::assertSame($memberPerson, $addedGroup->getMembers()[0]);
    }

    public function testImportRoleWithPersonAppliesRelationship(): void
    {
        $roleId = Ulid::generate();
        $personId = Ulid::generate();

        $rows = [
            [
                'id' => $roleId,
                'type' => 'ROLE',
                'name' => 'Manager Role',
                'assigned_person_id' => $personId,
                'group_member_ids' => '',
                'transport_configs' => '',
            ],
        ];

        $this->parser
            ->method('parse')
            ->willReturn(new ArrayIterator($rows));

        $assignedPerson = new Person('Assigned Person', Ulid::fromString($personId));
        $addedRole = null;

        $this->recipientRepository
            ->method('add')
            ->willReturnCallback(function (AbstractMessageRecipient $recipient) use (&$addedRole): void {
                if ($recipient instanceof Role) {
                    $addedRole = $recipient;
                }
            });

        $this->recipientRepository
            ->method('getRecipientFromID')
            ->willReturnCallback(function (Ulid $id) use ($roleId, $personId, $assignedPerson, &$addedRole): ?AbstractMessageRecipient {
                if ($id->toString() === $personId) {
                    return $assignedPerson;
                }
                if ($id->toString() === $roleId && $addedRole instanceof Role) {
                    return $addedRole;
                }

                return null;
            });

        $command = ImportRecipients::fromContent('', ExportFormat::CSV, ImportConflictStrategy::SKIP);

        $result = ($this->handler)($command);

        self::assertSame(1, $result->imported);
        self::assertInstanceOf(Role::class, $addedRole);
        self::assertSame($assignedPerson, $addedRole->person);
    }

    public function testImportMultipleRecipientTypes(): void
    {
        $personId = Ulid::generate();
        $groupId = Ulid::generate();
        $roleId = Ulid::generate();

        $rows = [
            [
                'id' => $personId,
                'type' => 'PERSON',
                'name' => 'Person 1',
                'assigned_person_id' => '',
                'group_member_ids' => '',
                'transport_configs' => '',
            ],
            [
                'id' => $groupId,
                'type' => 'GROUP',
                'name' => 'Group 1',
                'assigned_person_id' => '',
                'group_member_ids' => '',
                'transport_configs' => '',
            ],
            [
                'id' => $roleId,
                'type' => 'ROLE',
                'name' => 'Role 1',
                'assigned_person_id' => '',
                'group_member_ids' => '',
                'transport_configs' => '',
            ],
        ];

        $this->parser
            ->method('parse')
            ->willReturn(new ArrayIterator($rows));

        $this->recipientRepository
            ->method('getRecipientFromID')
            ->willReturn(null);

        $addedRecipients = [];
        $this->recipientRepository
            ->method('add')
            ->willReturnCallback(function (AbstractMessageRecipient $recipient) use (&$addedRecipients): void {
                $addedRecipients[] = $recipient;
            });

        $command = ImportRecipients::fromContent('', ExportFormat::CSV, ImportConflictStrategy::SKIP);

        $result = ($this->handler)($command);

        self::assertSame(3, $result->imported);
        self::assertCount(3, $addedRecipients);
        self::assertInstanceOf(Person::class, $addedRecipients[0]);
        self::assertInstanceOf(Group::class, $addedRecipients[1]);
        self::assertInstanceOf(Role::class, $addedRecipients[2]);
    }

    public function testImportWithTransportConfigsNewIdBasedFormat(): void
    {
        $id = Ulid::generate();
        $configId = Ulid::generate();
        $transportConfig = '{"'.$configId.'":{"key":"ntfy","enabled":true,"config":{"topic":"test-topic"}}}';

        $rows = [
            [
                'id' => $id,
                'type' => 'PERSON',
                'name' => 'Person With Config',
                'assigned_person_id' => '',
                'group_member_ids' => '',
                'transport_configs' => $transportConfig,
            ],
        ];

        $this->parser
            ->method('parse')
            ->willReturn(new ArrayIterator($rows));

        $this->recipientRepository
            ->method('getRecipientFromID')
            ->willReturn(null);

        $addedRecipient = null;
        $this->recipientRepository
            ->method('add')
            ->willReturnCallback(function (AbstractMessageRecipient $recipient) use (&$addedRecipient): void {
                $addedRecipient = $recipient;
            });

        $command = ImportRecipients::fromContent('', ExportFormat::CSV, ImportConflictStrategy::SKIP);

        $result = ($this->handler)($command);

        self::assertSame(1, $result->imported);
        self::assertInstanceOf(Person::class, $addedRecipient);

        $transportConfiguration = $addedRecipient->getFirstTransportConfigurationByKey('ntfy');
        self::assertNotNull($transportConfiguration);
        self::assertSame($configId, $transportConfiguration->getId()->toString());
        self::assertTrue($transportConfiguration->isEnabled);
        self::assertSame(['topic' => 'test-topic'], $transportConfiguration->getVendorSpecificConfig());
    }

    public function testImportWithMultipleTransportConfigsSameKey(): void
    {
        $id = Ulid::generate();
        $configId1 = Ulid::generate();
        $configId2 = Ulid::generate();
        $transportConfig = '{"'.$configId1.'":{"key":"ntfy","enabled":true,"config":{"topic":"primary"}},"'.$configId2.'":{"key":"ntfy","enabled":false,"config":{"topic":"backup"}}}';

        $rows = [
            [
                'id' => $id,
                'type' => 'PERSON',
                'name' => 'Person With Multiple Configs',
                'assigned_person_id' => '',
                'group_member_ids' => '',
                'transport_configs' => $transportConfig,
            ],
        ];

        $this->parser
            ->method('parse')
            ->willReturn(new ArrayIterator($rows));

        $this->recipientRepository
            ->method('getRecipientFromID')
            ->willReturn(null);

        $addedRecipient = null;
        $this->recipientRepository
            ->method('add')
            ->willReturnCallback(function (AbstractMessageRecipient $recipient) use (&$addedRecipient): void {
                $addedRecipient = $recipient;
            });

        $command = ImportRecipients::fromContent('', ExportFormat::CSV, ImportConflictStrategy::SKIP);

        $result = ($this->handler)($command);

        self::assertSame(1, $result->imported);
        self::assertInstanceOf(Person::class, $addedRecipient);

        $configs = $addedRecipient->getTransportConfiguration();
        self::assertCount(2, $configs);

        $configIds = array_map(fn ($c) => $c->getId()->toString(), $configs);
        self::assertContains($configId1, $configIds);
        self::assertContains($configId2, $configIds);
    }

    public function testImportUpdateExistingTransportConfigById(): void
    {
        $id = Ulid::generate();
        $configId = Ulid::generate();

        $existingPerson = new Person('Existing Person', Ulid::fromString($id));
        $existingConfig = $existingPerson->addTransportConfigurationWithId('ntfy', Ulid::fromString($configId));
        $existingConfig->isEnabled = false;
        $existingConfig->setVendorSpecificConfig(['topic' => 'old-topic']);

        $transportConfig = '{"'.$configId.'":{"key":"ntfy","enabled":true,"config":{"topic":"new-topic"}}}';

        $rows = [
            [
                'id' => $id,
                'type' => 'PERSON',
                'name' => 'Updated Person',
                'assigned_person_id' => '',
                'group_member_ids' => '',
                'transport_configs' => $transportConfig,
            ],
        ];

        $this->parser
            ->method('parse')
            ->willReturn(new ArrayIterator($rows));

        $this->recipientRepository
            ->method('getRecipientFromID')
            ->willReturn($existingPerson);

        $command = ImportRecipients::fromContent('', ExportFormat::CSV, ImportConflictStrategy::UPDATE);

        $result = ($this->handler)($command);

        self::assertSame(0, $result->imported);
        self::assertSame(1, $result->updated);

        $configs = $existingPerson->getTransportConfiguration();
        self::assertCount(1, $configs);
        self::assertSame($configId, $configs[0]->getId()->toString());
        self::assertTrue($configs[0]->isEnabled);
        self::assertSame(['topic' => 'new-topic'], $configs[0]->getVendorSpecificConfig());
    }

    public function testImportCreatesNewTransportConfigWhenIdNotFound(): void
    {
        $id = Ulid::generate();
        $existingConfigId = Ulid::generate();
        $newConfigId = Ulid::generate();

        $existingPerson = new Person('Existing Person', Ulid::fromString($id));
        $existingConfig = $existingPerson->addTransportConfigurationWithId('ntfy', Ulid::fromString($existingConfigId));
        $existingConfig->isEnabled = true;

        // Import with a different config ID - should create new config
        $transportConfig = '{"'.$newConfigId.'":{"key":"telegram","enabled":true,"config":{"chat_id":"123"}}}';

        $rows = [
            [
                'id' => $id,
                'type' => 'PERSON',
                'name' => 'Updated Person',
                'assigned_person_id' => '',
                'group_member_ids' => '',
                'transport_configs' => $transportConfig,
            ],
        ];

        $this->parser
            ->method('parse')
            ->willReturn(new ArrayIterator($rows));

        $this->recipientRepository
            ->method('getRecipientFromID')
            ->willReturn($existingPerson);

        $command = ImportRecipients::fromContent('', ExportFormat::CSV, ImportConflictStrategy::UPDATE);

        $result = ($this->handler)($command);

        self::assertSame(0, $result->imported);
        self::assertSame(1, $result->updated);

        $configs = $existingPerson->getTransportConfiguration();
        self::assertCount(2, $configs);

        $newConfig = $existingPerson->getTransportConfigurationById(Ulid::fromString($newConfigId));
        self::assertNotNull($newConfig);
        self::assertSame('telegram', $newConfig->getKey());
        self::assertSame(['chat_id' => '123'], $newConfig->getVendorSpecificConfig());
    }
}
