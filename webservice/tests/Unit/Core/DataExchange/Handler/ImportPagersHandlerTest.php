<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\DataExchange\Handler;

use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\DataExchange\Command\ImportPagers;
use App\Core\DataExchange\Handler\ImportPagersHandler;
use App\Core\DataExchange\Model\ExportFormat;
use App\Core\DataExchange\Model\ImportConflictStrategy;
use App\Core\DataExchange\Port\FormatAdapterFactory;
use App\Core\DataExchange\Port\ImportParser;
use App\Core\IntelPage\Model\CapCode;
use App\Core\IntelPage\Model\Channel;
use App\Core\IntelPage\Model\Pager;
use App\Core\IntelPage\Model\Slot;
use App\Core\IntelPage\Port\ChannelRepository;
use App\Core\IntelPage\Port\PagerRepository;
use App\Core\MessageRecipient\Model\Person;
use App\Core\MessageRecipient\Port\MessageRecipientRepository;
use ArrayIterator;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[CoversClass(ImportPagersHandler::class)]
#[CoversClass(ImportPagers::class)]
#[AllowMockObjectsWithoutExpectations]
final class ImportPagersHandlerTest extends TestCase
{
    private PagerRepository&MockObject $pagerRepository;
    private ChannelRepository&MockObject $channelRepository;
    private MessageRecipientRepository&MockObject $recipientRepository;
    private UnitOfWork&MockObject $uow;
    private ImportParser&MockObject $parser;
    private ImportPagersHandler $handler;

    protected function setUp(): void
    {
        $formatFactory = $this->createMock(FormatAdapterFactory::class);
        $this->pagerRepository = $this->createMock(PagerRepository::class);
        $this->channelRepository = $this->createMock(ChannelRepository::class);
        $this->recipientRepository = $this->createMock(MessageRecipientRepository::class);
        $this->uow = $this->createMock(UnitOfWork::class);
        $this->parser = $this->createMock(ImportParser::class);

        $formatFactory
            ->method('createParser')
            ->willReturn($this->parser);

        $this->handler = new ImportPagersHandler(
            $formatFactory,
            $this->pagerRepository,
            $this->channelRepository,
            $this->recipientRepository,
            $this->uow,
        );
    }

    public function testImportNewPager(): void
    {
        $id = Ulid::generate();

        $rows = [
            [
                'id' => $id,
                'label' => 'Pager 1',
                'number' => '101',
                'comment' => 'Test pager',
                'activated' => '1',
                'carried_by_id' => '',
                'slot_assignments' => '',
            ],
        ];

        $this->parser
            ->method('parse')
            ->willReturn(new ArrayIterator($rows));

        $this->pagerRepository
            ->method('getById')
            ->willReturn(null);

        $persistedPager = null;
        $this->pagerRepository
            ->expects(self::once())
            ->method('persist')
            ->willReturnCallback(function (Pager $pager) use (&$persistedPager): void {
                $persistedPager = $pager;
            });

        $this->uow
            ->expects(self::once())
            ->method('commit');

        $command = ImportPagers::fromContent('', ExportFormat::CSV, ImportConflictStrategy::SKIP);

        $result = ($this->handler)($command);

        self::assertSame(1, $result->imported);
        self::assertSame(0, $result->updated);
        self::assertSame(0, $result->skippedCount);
        self::assertEmpty($result->errors);
        self::assertInstanceOf(Pager::class, $persistedPager);
        self::assertSame('Pager 1', $persistedPager->getLabel());
        self::assertSame(101, $persistedPager->getNumber());
        self::assertTrue($persistedPager->isActivated());
    }

    public function testImportPagerWithComment(): void
    {
        $id = Ulid::generate();

        $rows = [
            [
                'id' => $id,
                'label' => 'Pager With Comment',
                'number' => '102',
                'comment' => 'This is a detailed comment',
                'activated' => '0',
                'carried_by_id' => '',
                'slot_assignments' => '',
            ],
        ];

        $this->parser
            ->method('parse')
            ->willReturn(new ArrayIterator($rows));

        $this->pagerRepository
            ->method('getById')
            ->willReturn(null);

        $persistedPager = null;
        $this->pagerRepository
            ->method('persist')
            ->willReturnCallback(function (Pager $pager) use (&$persistedPager): void {
                $persistedPager = $pager;
            });

        $command = ImportPagers::fromContent('', ExportFormat::CSV, ImportConflictStrategy::SKIP);

        $result = ($this->handler)($command);

        self::assertSame(1, $result->imported);
        self::assertNotNull($persistedPager);
        self::assertSame('This is a detailed comment', $persistedPager->getComment());
        self::assertFalse($persistedPager->isActivated());
    }

    public function testImportPagerWithCarrier(): void
    {
        $pagerId = Ulid::generate();
        $carrierId = Ulid::generate();

        $carrier = new Person('Carrier Person', Ulid::fromString($carrierId));

        $rows = [
            [
                'id' => $pagerId,
                'label' => 'Carried Pager',
                'number' => '103',
                'comment' => '',
                'activated' => '1',
                'carried_by_id' => $carrierId,
                'slot_assignments' => '',
            ],
        ];

        $this->parser
            ->method('parse')
            ->willReturn(new ArrayIterator($rows));

        $this->pagerRepository
            ->method('getById')
            ->willReturn(null);

        $this->recipientRepository
            ->method('getRecipientFromID')
            ->with(self::callback(fn (Ulid $id): bool => $id->toBase32() === $carrierId))
            ->willReturn($carrier);

        $persistedPager = null;
        $this->pagerRepository
            ->method('persist')
            ->willReturnCallback(function (Pager $pager) use (&$persistedPager): void {
                $persistedPager = $pager;
            });

        $command = ImportPagers::fromContent('', ExportFormat::CSV, ImportConflictStrategy::SKIP);

        $result = ($this->handler)($command);

        self::assertSame(1, $result->imported);
        self::assertNotNull($persistedPager);
        self::assertSame($carrier, $persistedPager->getCarriedBy());
    }

    public function testImportWithSkipStrategySkipsExisting(): void
    {
        $id = Ulid::generate();
        $existingPager = new Pager(Ulid::fromString($id), 'Existing Pager', 100);

        $rows = [
            [
                'id' => $id,
                'label' => 'Updated Label',
                'number' => '200',
                'comment' => '',
                'activated' => '1',
                'carried_by_id' => '',
                'slot_assignments' => '',
            ],
        ];

        $this->parser
            ->method('parse')
            ->willReturn(new ArrayIterator($rows));

        $this->pagerRepository
            ->method('getById')
            ->willReturn($existingPager);

        $this->pagerRepository
            ->expects(self::never())
            ->method('persist');

        $command = ImportPagers::fromContent('', ExportFormat::CSV, ImportConflictStrategy::SKIP);

        $result = ($this->handler)($command);

        self::assertSame(0, $result->imported);
        self::assertSame(0, $result->updated);
        self::assertSame(1, $result->skippedCount);
        self::assertSame('Existing Pager', $existingPager->getLabel());
    }

    public function testImportWithUpdateStrategyUpdatesExisting(): void
    {
        $id = Ulid::generate();
        $existingPager = new Pager(Ulid::fromString($id), 'Old Label', 100);

        $rows = [
            [
                'id' => $id,
                'label' => 'New Label',
                'number' => '200',
                'comment' => 'Updated comment',
                'activated' => '1',
                'carried_by_id' => '',
                'slot_assignments' => '',
            ],
        ];

        $this->parser
            ->method('parse')
            ->willReturn(new ArrayIterator($rows));

        $this->pagerRepository
            ->method('getById')
            ->willReturn($existingPager);

        $this->pagerRepository
            ->expects(self::once())
            ->method('persist')
            ->with($existingPager);

        $command = ImportPagers::fromContent('', ExportFormat::CSV, ImportConflictStrategy::UPDATE);

        $result = ($this->handler)($command);

        self::assertSame(0, $result->imported);
        self::assertSame(1, $result->updated);
        self::assertSame(0, $result->skippedCount);
        self::assertSame('New Label', $existingPager->getLabel());
        self::assertSame(200, $existingPager->getNumber());
        self::assertSame('Updated comment', $existingPager->getComment());
    }

    public function testImportWithErrorStrategyReportsErrorOnExisting(): void
    {
        $id = Ulid::generate();
        $existingPager = new Pager(Ulid::fromString($id), 'Existing Pager', 100);

        $rows = [
            [
                'id' => $id,
                'label' => 'Duplicate',
                'number' => '200',
                'comment' => '',
                'activated' => '1',
                'carried_by_id' => '',
                'slot_assignments' => '',
            ],
        ];

        $this->parser
            ->method('parse')
            ->willReturn(new ArrayIterator($rows));

        $this->pagerRepository
            ->method('getById')
            ->willReturn($existingPager);

        $command = ImportPagers::fromContent('', ExportFormat::CSV, ImportConflictStrategy::ERROR);

        $result = ($this->handler)($command);

        self::assertSame(0, $result->imported);
        self::assertSame(0, $result->updated);
        self::assertCount(1, $result->errors);
        self::assertStringContainsString('already exists', $result->errors[0]);
    }

    public function testImportWithMissingColumnsReportsError(): void
    {
        $rows = [
            ['id' => Ulid::generate(), 'label' => 'Incomplete'],
            // Missing number, comment, activated, carried_by_id, slot_assignments
        ];

        $this->parser
            ->method('parse')
            ->willReturn(new ArrayIterator($rows));

        $command = ImportPagers::fromContent('', ExportFormat::CSV, ImportConflictStrategy::SKIP);

        $result = ($this->handler)($command);

        self::assertSame(0, $result->imported);
        self::assertCount(1, $result->errors);
        self::assertStringContainsString('Missing required columns', $result->errors[0]);
    }

    public function testImportFromFileUsesParseFile(): void
    {
        $id = Ulid::generate();
        $rows = [
            [
                'id' => $id,
                'label' => 'File Pager',
                'number' => '104',
                'comment' => '',
                'activated' => '1',
                'carried_by_id' => '',
                'slot_assignments' => '',
            ],
        ];

        $this->parser
            ->expects(self::once())
            ->method('parseFile')
            ->with('/path/to/pagers.csv')
            ->willReturn(new ArrayIterator($rows));

        $this->pagerRepository
            ->method('getById')
            ->willReturn(null);

        $command = ImportPagers::fromFile('/path/to/pagers.csv', ExportFormat::CSV, ImportConflictStrategy::SKIP);

        $result = ($this->handler)($command);

        self::assertSame(1, $result->imported);
    }

    public function testImportWithIndividualSlotAssignment(): void
    {
        $id = Ulid::generate();
        $slotData = json_encode([
            ['slot' => 0, 'type' => 'individual', 'capCode' => 1234, 'audible' => true, 'vibration' => false],
        ]);

        $rows = [
            [
                'id' => $id,
                'label' => 'Pager With Slot',
                'number' => '105',
                'comment' => '',
                'activated' => '1',
                'carried_by_id' => '',
                'slot_assignments' => $slotData,
            ],
        ];

        $this->parser
            ->method('parse')
            ->willReturn(new ArrayIterator($rows));

        $this->pagerRepository
            ->method('getById')
            ->willReturn(null);

        $persistedPager = null;
        $this->pagerRepository
            ->method('persist')
            ->willReturnCallback(function (Pager $pager) use (&$persistedPager): void {
                $persistedPager = $pager;
            });

        $command = ImportPagers::fromContent('', ExportFormat::CSV, ImportConflictStrategy::SKIP, importSlotAssignments: true);

        $result = ($this->handler)($command);

        self::assertSame(1, $result->imported, 'Failed with: '.json_encode($result->errors));
        self::assertInstanceOf(Pager::class, $persistedPager);
        $assignment = $persistedPager->getCapAssignment(Slot::fromInt(0));
        self::assertNotNull($assignment);
    }

    public function testImportWithChannelSlotAssignment(): void
    {
        $pagerId = Ulid::generate();
        $channelId = Ulid::generate();
        $channel = new Channel(Ulid::fromString($channelId), 'Test Channel', CapCode::fromInt(1234), true, false);

        $slotData = json_encode([
            ['slot' => 1, 'type' => 'channel', 'channelId' => $channelId],
        ]);

        $rows = [
            [
                'id' => $pagerId,
                'label' => 'Pager With Channel',
                'number' => '106',
                'comment' => '',
                'activated' => '1',
                'carried_by_id' => '',
                'slot_assignments' => $slotData,
            ],
        ];

        $this->parser
            ->method('parse')
            ->willReturn(new ArrayIterator($rows));

        $this->pagerRepository
            ->method('getById')
            ->willReturn(null);

        $this->channelRepository
            ->method('getById')
            ->with(self::callback(fn (Ulid $id): bool => $id->toBase32() === $channelId))
            ->willReturn($channel);

        $persistedPager = null;
        $this->pagerRepository
            ->method('persist')
            ->willReturnCallback(function (Pager $pager) use (&$persistedPager): void {
                $persistedPager = $pager;
            });

        $command = ImportPagers::fromContent('', ExportFormat::CSV, ImportConflictStrategy::SKIP, importSlotAssignments: true);

        $result = ($this->handler)($command);

        self::assertSame(1, $result->imported);
        self::assertNotNull($persistedPager);
        $assignment = $persistedPager->getCapAssignment(Slot::fromInt(1));
        self::assertNotNull($assignment);
    }

    public function testImportWithSlotAssignmentsDisabled(): void
    {
        $id = Ulid::generate();
        $slotData = json_encode([
            ['slot' => 0, 'type' => 'individual', 'capCode' => 12345, 'audible' => true, 'vibration' => false],
        ]);

        $rows = [
            [
                'id' => $id,
                'label' => 'Pager Without Slots',
                'number' => '107',
                'comment' => '',
                'activated' => '1',
                'carried_by_id' => '',
                'slot_assignments' => $slotData,
            ],
        ];

        $this->parser
            ->method('parse')
            ->willReturn(new ArrayIterator($rows));

        $this->pagerRepository
            ->method('getById')
            ->willReturn(null);

        $persistedPager = null;
        $this->pagerRepository
            ->method('persist')
            ->willReturnCallback(function (Pager $pager) use (&$persistedPager): void {
                $persistedPager = $pager;
            });

        $command = ImportPagers::fromContent('', ExportFormat::CSV, ImportConflictStrategy::SKIP, importSlotAssignments: false);

        $result = ($this->handler)($command);

        self::assertSame(1, $result->imported);
        self::assertInstanceOf(Pager::class, $persistedPager);
        $assignment = $persistedPager->getCapAssignment(Slot::fromInt(0));
        self::assertNull($assignment);
    }

    public function testImportMultiplePagers(): void
    {
        $id1 = Ulid::generate();
        $id2 = Ulid::generate();
        $id3 = Ulid::generate();

        $rows = [
            [
                'id' => $id1,
                'label' => 'Pager 1',
                'number' => '101',
                'comment' => '',
                'activated' => '1',
                'carried_by_id' => '',
                'slot_assignments' => '',
            ],
            [
                'id' => $id2,
                'label' => 'Pager 2',
                'number' => '102',
                'comment' => '',
                'activated' => '1',
                'carried_by_id' => '',
                'slot_assignments' => '',
            ],
            [
                'id' => $id3,
                'label' => 'Pager 3',
                'number' => '103',
                'comment' => '',
                'activated' => '0',
                'carried_by_id' => '',
                'slot_assignments' => '',
            ],
        ];

        $this->parser
            ->method('parse')
            ->willReturn(new ArrayIterator($rows));

        $this->pagerRepository
            ->method('getById')
            ->willReturn(null);

        $persistedPagers = [];
        $this->pagerRepository
            ->method('persist')
            ->willReturnCallback(function (Pager $pager) use (&$persistedPagers): void {
                $persistedPagers[] = $pager;
            });

        $command = ImportPagers::fromContent('', ExportFormat::CSV, ImportConflictStrategy::SKIP);

        $result = ($this->handler)($command);

        self::assertSame(3, $result->imported);
        self::assertCount(3, $persistedPagers);
        self::assertSame('Pager 1', $persistedPagers[0]->getLabel());
        self::assertSame('Pager 2', $persistedPagers[1]->getLabel());
        self::assertSame('Pager 3', $persistedPagers[2]->getLabel());
    }

    public function testUpdateClearsCarrierWhenEmpty(): void
    {
        $pagerId = Ulid::generate();
        $carrierId = Ulid::generate();
        $carrier = new Person('Old Carrier', Ulid::fromString($carrierId));

        $existingPager = new Pager(Ulid::fromString($pagerId), 'Existing', 100);
        $existingPager->setCarriedBy($carrier);

        $rows = [
            [
                'id' => $pagerId,
                'label' => 'Updated',
                'number' => '100',
                'comment' => '',
                'activated' => '1',
                'carried_by_id' => '',
                'slot_assignments' => '',
            ],
        ];

        $this->parser
            ->method('parse')
            ->willReturn(new ArrayIterator($rows));

        $this->pagerRepository
            ->method('getById')
            ->willReturn($existingPager);

        $command = ImportPagers::fromContent('', ExportFormat::CSV, ImportConflictStrategy::UPDATE);

        $result = ($this->handler)($command);

        self::assertSame(1, $result->updated);
        self::assertNull($existingPager->getCarriedBy());
    }
}
