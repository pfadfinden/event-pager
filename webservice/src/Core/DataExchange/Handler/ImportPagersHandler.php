<?php

declare(strict_types=1);

namespace App\Core\DataExchange\Handler;

use App\Core\Contracts\Bus\Bus;
use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\DataExchange\Command\ImportPagers;
use App\Core\DataExchange\Exception\ImportValidationException;
use App\Core\DataExchange\Model\ImportConflictStrategy;
use App\Core\DataExchange\Model\ImportResult;
use App\Core\DataExchange\Port\FormatAdapterFactory;
use App\Core\DataExchange\ReadModel\PagerExportRow;
use App\Core\IntelPage\Model\CapCode;
use App\Core\IntelPage\Model\Channel;
use App\Core\IntelPage\Model\Pager;
use App\Core\IntelPage\Model\Slot;
use App\Core\IntelPage\Port\ChannelRepository;
use App\Core\IntelPage\Port\PagerRepository;
use App\Core\MessageRecipient\Port\MessageRecipientRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Uid\Ulid;
use function is_array;
use function json_decode;
use function sprintf;
use const JSON_THROW_ON_ERROR;

#[AsMessageHandler(bus: Bus::COMMAND)]
final class ImportPagersHandler extends AbstractImportHandler
{
    public function __construct(
        FormatAdapterFactory $formatFactory,
        private readonly PagerRepository $pagerRepository,
        private readonly ChannelRepository $channelRepository,
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
        return PagerExportRow::csvHeaders();
    }

    protected function getIdentifierField(): string
    {
        return 'id';
    }

    public function __invoke(ImportPagers $command): ImportResult
    {
        return $this->executeImport(
            $command->filePath,
            $command->content,
            $command->format,
            $command->conflictStrategy,
            fn (array $row, int $index): string => $this->processRow($row, $command->conflictStrategy, $command->importSlotAssignments),
        );
    }

    /**
     * @param array<string, string> $row
     */
    private function processRow(array $row, ImportConflictStrategy $strategy, bool $importSlots): string
    {
        $id = Ulid::fromString($row['id']);
        $existing = $this->pagerRepository->getById($id);

        if ($existing instanceof Pager) {
            return match ($strategy) {
                ImportConflictStrategy::SKIP => 'skipped',
                ImportConflictStrategy::UPDATE => $this->updatePager($existing, $row, $importSlots),
                ImportConflictStrategy::ERROR => throw new ImportValidationException(sprintf('Pager with ID %s already exists', $row['id'])),
            };
        }

        $this->createPager($row, $id, $importSlots);

        return 'imported';
    }

    /**
     * @param array<string, string> $row
     */
    private function createPager(array $row, Ulid $id, bool $importSlots): void
    {
        $pager = new Pager($id, $row['label'], (int) $row['number']);
        $pager->setComment('' !== $row['comment'] ? $row['comment'] : null);
        $pager->setActivated($this->parseBool($row['activated']));

        if ('' !== $row['carried_by_id']) {
            $carrier = $this->recipientRepository->getRecipientFromID(Ulid::fromString($row['carried_by_id']));
            $pager->setCarriedBy($carrier);
        }

        if ($importSlots && '' !== $row['slot_assignments']) {
            $this->applySlotAssignments($pager, $row['slot_assignments']);
        }

        $this->pagerRepository->persist($pager);
    }

    /**
     * @param array<string, string> $row
     */
    private function updatePager(Pager $pager, array $row, bool $importSlots): string
    {
        $pager->setLabel($row['label']);
        $pager->setNumber((int) $row['number']);
        $pager->setComment('' !== $row['comment'] ? $row['comment'] : null);
        $pager->setActivated($this->parseBool($row['activated']));

        if ('' !== $row['carried_by_id']) {
            $carrier = $this->recipientRepository->getRecipientFromID(Ulid::fromString($row['carried_by_id']));
            $pager->setCarriedBy($carrier);
        } else {
            $pager->setCarriedBy(null);
        }

        if ($importSlots) {
            // Clear existing slots first
            for ($i = Pager::PAGER_SLOT_MIN; $i <= Pager::PAGER_SLOT_MAX; ++$i) {
                $pager->clearSlot(Slot::fromInt($i));
            }

            if ('' !== $row['slot_assignments']) {
                $this->applySlotAssignments($pager, $row['slot_assignments']);
            }
        }

        $this->pagerRepository->persist($pager);

        return 'updated';
    }

    private function applySlotAssignments(Pager $pager, string $slotsJson): void
    {
        /** @var array<int, array{slot: int, type: string, capCode?: int, audible?: bool, vibration?: bool, channelId?: string}>|null $slots */
        $slots = json_decode($slotsJson, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($slots)) {
            return;
        }

        foreach ($slots as $slotData) {
            $slot = Slot::fromInt($slotData['slot']);

            if ('individual' === $slotData['type']) {
                $pager->assignIndividualCap(
                    $slot,
                    CapCode::fromInt($slotData['capCode'] ?? 0),
                    $slotData['audible'] ?? false,
                    $slotData['vibration'] ?? false,
                );
            } elseif ('channel' === $slotData['type'] && isset($slotData['channelId'])) {
                $channel = $this->channelRepository->getById(Ulid::fromString($slotData['channelId']));
                if ($channel instanceof Channel) {
                    $pager->assignChannel($slot, $channel);
                }
            }
        }
    }
}
