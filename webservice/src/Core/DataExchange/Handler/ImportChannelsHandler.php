<?php

declare(strict_types=1);

namespace App\Core\DataExchange\Handler;

use App\Core\Contracts\Bus\Bus;
use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\DataExchange\Command\ImportChannels;
use App\Core\DataExchange\Exception\ImportValidationException;
use App\Core\DataExchange\Model\ImportConflictStrategy;
use App\Core\DataExchange\Model\ImportResult;
use App\Core\DataExchange\Port\FormatAdapterFactory;
use App\Core\DataExchange\ReadModel\ChannelExportRow;
use App\Core\IntelPage\Model\CapCode;
use App\Core\IntelPage\Model\Channel;
use App\Core\IntelPage\Port\ChannelRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Uid\Ulid;
use function sprintf;

#[AsMessageHandler(bus: Bus::COMMAND)]
final class ImportChannelsHandler extends AbstractImportHandler
{
    public function __construct(
        FormatAdapterFactory $formatFactory,
        private readonly ChannelRepository $channelRepository,
        UnitOfWork $uow,
    ) {
        parent::__construct($formatFactory, $uow);
    }

    /**
     * @return string[]
     */
    protected function getRequiredHeaders(): array
    {
        return ChannelExportRow::csvHeaders();
    }

    protected function getIdentifierField(): string
    {
        return 'id';
    }

    public function __invoke(ImportChannels $command): ImportResult
    {
        return $this->executeImport(
            $command->filePath,
            $command->content,
            $command->format,
            $command->conflictStrategy,
            fn (array $row, int $index): string => $this->processRow($row, $command->conflictStrategy),
        );
    }

    /**
     * @param array<string, string> $row
     */
    private function processRow(array $row, ImportConflictStrategy $strategy): string
    {
        $id = Ulid::fromString($row['id']);
        $existing = $this->channelRepository->getById($id);

        if ($existing instanceof Channel) {
            return match ($strategy) {
                ImportConflictStrategy::SKIP => 'skipped',
                ImportConflictStrategy::UPDATE => $this->updateChannel($existing, $row),
                ImportConflictStrategy::ERROR => throw new ImportValidationException(sprintf('Channel with ID %s already exists', $row['id'])),
            };
        }

        $this->createChannel($row, $id);

        return 'imported';
    }

    /**
     * @param array<string, string> $row
     */
    private function createChannel(array $row, Ulid $id): void
    {
        $channel = new Channel(
            $id,
            $row['name'],
            CapCode::fromInt((int) $row['cap_code']),
            $this->parseBool($row['audible']),
            $this->parseBool($row['vibration']),
        );
        $this->channelRepository->persist($channel);
    }

    /**
     * @param array<string, string> $row
     */
    private function updateChannel(Channel $channel, array $row): string
    {
        $channel->setName($row['name']);
        $channel->setCapCode(CapCode::fromInt((int) $row['cap_code']));
        $channel->setAudible($this->parseBool($row['audible']));
        $channel->setVibration($this->parseBool($row['vibration']));
        $this->channelRepository->persist($channel);

        return 'updated';
    }
}
