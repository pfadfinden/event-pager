<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\DataExchange\Handler;

use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\DataExchange\Command\ImportChannels;
use App\Core\DataExchange\Handler\ImportChannelsHandler;
use App\Core\DataExchange\Model\ExportFormat;
use App\Core\DataExchange\Model\ImportConflictStrategy;
use App\Core\DataExchange\Port\FormatAdapterFactory;
use App\Core\DataExchange\Port\ImportParser;
use App\Core\IntelPage\Model\CapCode;
use App\Core\IntelPage\Model\Channel;
use App\Core\IntelPage\Port\ChannelRepository;
use ArrayIterator;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[CoversClass(ImportChannelsHandler::class)]
#[CoversClass(ImportChannels::class)]
#[AllowMockObjectsWithoutExpectations]
final class ImportChannelsHandlerTest extends TestCase
{
    private ChannelRepository&MockObject $channelRepository;
    private UnitOfWork&MockObject $uow;
    private ImportParser&MockObject $parser;
    private ImportChannelsHandler $handler;

    protected function setUp(): void
    {
        $formatFactory = $this->createMock(FormatAdapterFactory::class);
        $this->channelRepository = $this->createMock(ChannelRepository::class);
        $this->uow = $this->createMock(UnitOfWork::class);
        $this->parser = $this->createMock(ImportParser::class);

        $formatFactory
            ->method('createParser')
            ->willReturn($this->parser);

        $this->handler = new ImportChannelsHandler(
            $formatFactory,
            $this->channelRepository,
            $this->uow,
        );
    }

    public function testImportNewChannels(): void
    {
        $id1 = Ulid::generate();
        $id2 = Ulid::generate();

        $rows = [
            ['id' => $id1, 'name' => 'Channel 1', 'cap_code' => '1001', 'audible' => '1', 'vibration' => '0'],
            ['id' => $id2, 'name' => 'Channel 2', 'cap_code' => '1002', 'audible' => 'true', 'vibration' => 'true'],
        ];

        $this->parser
            ->method('parse')
            ->willReturn(new ArrayIterator($rows));

        $this->channelRepository
            ->method('getById')
            ->willReturn(null);

        $this->channelRepository
            ->expects(self::exactly(2))
            ->method('persist')
            ->with(self::isInstanceOf(Channel::class));

        $this->uow
            ->expects(self::once())
            ->method('commit');

        $command = ImportChannels::fromContent(
            implode("\n", array_map(fn (array $r): string => implode(',', $r), $rows)),
            ExportFormat::CSV,
            ImportConflictStrategy::SKIP,
        );

        $result = ($this->handler)($command);

        self::assertSame(2, $result->imported);
        self::assertSame(0, $result->updated);
        self::assertSame(0, $result->skippedCount);
        self::assertEmpty($result->errors);
    }

    public function testImportWithSkipStrategySkipsExisting(): void
    {
        $id = Ulid::generate();
        $existingChannel = new Channel(
            Ulid::fromString($id),
            'Existing Channel',
            CapCode::fromInt(1001),
            true,
            true,
        );

        $rows = [
            ['id' => $id, 'name' => 'Updated Name', 'cap_code' => '1001', 'audible' => '1', 'vibration' => '1'],
        ];

        $this->parser
            ->method('parse')
            ->willReturn(new ArrayIterator($rows));

        $this->channelRepository
            ->method('getById')
            ->willReturn($existingChannel);

        $this->channelRepository
            ->expects(self::never())
            ->method('persist');

        $command = ImportChannels::fromContent('', ExportFormat::CSV, ImportConflictStrategy::SKIP);

        $result = ($this->handler)($command);

        self::assertSame(0, $result->imported);
        self::assertSame(0, $result->updated);
        self::assertSame(1, $result->skippedCount);
    }

    public function testImportWithUpdateStrategyUpdatesExisting(): void
    {
        $id = Ulid::generate();
        $existingChannel = new Channel(
            Ulid::fromString($id),
            'Old Name',
            CapCode::fromInt(1001),
            false,
            false,
        );

        $rows = [
            ['id' => $id, 'name' => 'New Name', 'cap_code' => '2002', 'audible' => '1', 'vibration' => '1'],
        ];

        $this->parser
            ->method('parse')
            ->willReturn(new ArrayIterator($rows));

        $this->channelRepository
            ->method('getById')
            ->willReturn($existingChannel);

        $this->channelRepository
            ->expects(self::once())
            ->method('persist')
            ->with($existingChannel);

        $command = ImportChannels::fromContent('', ExportFormat::CSV, ImportConflictStrategy::UPDATE);

        $result = ($this->handler)($command);

        self::assertSame(0, $result->imported);
        self::assertSame(1, $result->updated);
        self::assertSame(0, $result->skippedCount);

        // Verify the channel was updated
        self::assertSame('New Name', $existingChannel->getName());
        self::assertTrue($existingChannel->isAudible());
        self::assertTrue($existingChannel->isVibration());
    }

    public function testImportWithErrorStrategyThrowsOnExisting(): void
    {
        $id = Ulid::generate();
        $existingChannel = new Channel(
            Ulid::fromString($id),
            'Existing Channel',
            CapCode::fromInt(1001),
            true,
            true,
        );

        $rows = [
            ['id' => $id, 'name' => 'Duplicate', 'cap_code' => '1001', 'audible' => '1', 'vibration' => '1'],
        ];

        $this->parser
            ->method('parse')
            ->willReturn(new ArrayIterator($rows));

        $this->channelRepository
            ->method('getById')
            ->willReturn($existingChannel);

        $command = ImportChannels::fromContent('', ExportFormat::CSV, ImportConflictStrategy::ERROR);

        $result = ($this->handler)($command);

        self::assertSame(0, $result->imported);
        self::assertSame(0, $result->updated);
        self::assertCount(1, $result->errors);
        self::assertStringContainsString('already exists', $result->errors[0]);
    }

    public function testImportWithMissingColumnsReportsError(): void
    {
        $rows = [
            ['id' => Ulid::generate(), 'name' => 'Missing columns'],
            // Missing cap_code, audible, vibration
        ];

        $this->parser
            ->method('parse')
            ->willReturn(new ArrayIterator($rows));

        $command = ImportChannels::fromContent('', ExportFormat::CSV, ImportConflictStrategy::SKIP);

        $result = ($this->handler)($command);

        self::assertSame(0, $result->imported);
        self::assertCount(1, $result->errors);
        self::assertStringContainsString('Missing required columns', $result->errors[0]);
    }

    public function testImportFromFileUsesParseFile(): void
    {
        $id = Ulid::generate();
        $rows = [
            ['id' => $id, 'name' => 'Channel', 'cap_code' => '1001', 'audible' => '1', 'vibration' => '1'],
        ];

        $this->parser
            ->expects(self::once())
            ->method('parseFile')
            ->with('/path/to/file.csv')
            ->willReturn(new ArrayIterator($rows));

        $this->channelRepository
            ->method('getById')
            ->willReturn(null);

        $command = ImportChannels::fromFile('/path/to/file.csv', ExportFormat::CSV, ImportConflictStrategy::SKIP);

        $result = ($this->handler)($command);

        self::assertSame(1, $result->imported);
    }

    public function testParseBoolHandlesVariousFormats(): void
    {
        $id1 = Ulid::generate();
        $id2 = Ulid::generate();
        $id3 = Ulid::generate();
        $id4 = Ulid::generate();

        $rows = [
            ['id' => $id1, 'name' => 'Ch1', 'cap_code' => '1001', 'audible' => '1', 'vibration' => '0'],
            ['id' => $id2, 'name' => 'Ch2', 'cap_code' => '1002', 'audible' => 'true', 'vibration' => 'false'],
            ['id' => $id3, 'name' => 'Ch3', 'cap_code' => '1003', 'audible' => 'TRUE', 'vibration' => 'FALSE'],
            ['id' => $id4, 'name' => 'Ch4', 'cap_code' => '1004', 'audible' => '0', 'vibration' => '1'],
        ];

        $this->parser
            ->method('parse')
            ->willReturn(new ArrayIterator($rows));

        $this->channelRepository
            ->method('getById')
            ->willReturn(null);

        $persistedChannels = [];
        $this->channelRepository
            ->method('persist')
            ->willReturnCallback(function (Channel $channel) use (&$persistedChannels): void {
                $persistedChannels[] = $channel;
            });

        $command = ImportChannels::fromContent('', ExportFormat::CSV, ImportConflictStrategy::SKIP);

        ($this->handler)($command);

        self::assertCount(4, $persistedChannels);
        // '1' -> true, '0' -> false
        self::assertTrue($persistedChannels[0]->isAudible());
        self::assertFalse($persistedChannels[0]->isVibration());
        // 'true' -> true, 'false' -> false
        self::assertTrue($persistedChannels[1]->isAudible());
        self::assertFalse($persistedChannels[1]->isVibration());
        // 'TRUE' -> true (case insensitive), 'FALSE' -> false
        self::assertTrue($persistedChannels[2]->isAudible());
        self::assertFalse($persistedChannels[2]->isVibration());
        // '0' -> false, '1' -> true
        self::assertFalse($persistedChannels[3]->isAudible());
        self::assertTrue($persistedChannels[3]->isVibration());
    }
}
