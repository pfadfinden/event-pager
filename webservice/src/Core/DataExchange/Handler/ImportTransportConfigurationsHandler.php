<?php

declare(strict_types=1);

namespace App\Core\DataExchange\Handler;

use App\Core\Contracts\Bus\Bus;
use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\DataExchange\Command\ImportTransportConfigurations;
use App\Core\DataExchange\Exception\ImportValidationException;
use App\Core\DataExchange\Model\ImportConflictStrategy;
use App\Core\DataExchange\Model\ImportResult;
use App\Core\DataExchange\Port\FormatAdapterFactory;
use App\Core\DataExchange\ReadModel\TransportConfigurationExportRow;
use App\Core\TransportManager\Model\TransportConfiguration;
use App\Core\TransportManager\Port\TransportConfigurationRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use function json_decode;
use function sprintf;
use const JSON_THROW_ON_ERROR;

#[AsMessageHandler(bus: Bus::COMMAND)]
final class ImportTransportConfigurationsHandler extends AbstractImportHandler
{
    public function __construct(
        FormatAdapterFactory $formatFactory,
        private readonly TransportConfigurationRepository $transportRepository,
        UnitOfWork $uow,
    ) {
        parent::__construct($formatFactory, $uow);
    }

    /**
     * @return string[]
     */
    protected function getRequiredHeaders(): array
    {
        return TransportConfigurationExportRow::csvHeaders();
    }

    protected function getIdentifierField(): string
    {
        return 'key';
    }

    public function __invoke(ImportTransportConfigurations $command): ImportResult
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
        $key = $row['key'];
        $existing = $this->transportRepository->getByKey($key);

        if ($existing instanceof TransportConfiguration) {
            return match ($strategy) {
                ImportConflictStrategy::SKIP => 'skipped',
                ImportConflictStrategy::UPDATE => $this->updateTransport($existing, $row),
                ImportConflictStrategy::ERROR => throw new ImportValidationException(sprintf('Transport configuration with key %s already exists', $key)),
            };
        }

        $this->createTransport($row);

        return 'imported';
    }

    /**
     * @param array<string, string> $row
     */
    private function createTransport(array $row): void
    {
        /** @var class-string $transport */
        $transport = $row['transport'];
        $config = new TransportConfiguration(
            $row['key'],
            $transport,
            $row['title'],
        );
        $config->setEnabled($this->parseBool($row['enabled']));

        if ('' !== $row['vendor_specific_config']) {
            /** @var array<string, mixed>|null $vendorConfig */
            $vendorConfig = json_decode($row['vendor_specific_config'], true, 512, JSON_THROW_ON_ERROR);
            $config->setVendorSpecificConfig($vendorConfig);
        }

        $this->transportRepository->persist($config);
    }

    /**
     * @param array<string, string> $row
     */
    private function updateTransport(TransportConfiguration $config, array $row): string
    {
        /** @var class-string $transport */
        $transport = $row['transport'];
        $config->setTransport($transport);
        $config->setTitle($row['title']);
        $config->setEnabled($this->parseBool($row['enabled']));

        if ('' !== $row['vendor_specific_config']) {
            /** @var array<string, mixed>|null $vendorConfig */
            $vendorConfig = json_decode($row['vendor_specific_config'], true, 512, JSON_THROW_ON_ERROR);
            $config->setVendorSpecificConfig($vendorConfig);
        } else {
            $config->setVendorSpecificConfig(null);
        }

        $this->transportRepository->persist($config);

        return 'updated';
    }
}
