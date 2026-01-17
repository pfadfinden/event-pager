<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\DoctrineORM\Query;

use App\Core\DataExchange\Query\ExportTransportConfigurations;
use App\Core\DataExchange\ReadModel\TransportConfigurationExportRow;
use App\Core\TransportManager\Model\TransportConfiguration;
use Doctrine\ORM\EntityManagerInterface;
use function json_encode;
use const JSON_THROW_ON_ERROR;

final readonly class ExportTransportConfigurationsQueryHandler
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    /**
     * @return iterable<TransportConfigurationExportRow>
     */
    public function __invoke(ExportTransportConfigurations $query): iterable
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('t')
            ->from(TransportConfiguration::class, 't')
            ->orderBy('t.key', 'ASC');

        if (true === $query->enabledOnly) {
            $qb->where('t.enabled = true');
        }

        foreach ($qb->getQuery()->toIterable() as $transport) {
            /** @var TransportConfiguration $transport */
            $vendorConfig = $transport->getVendorSpecificConfig();

            yield new TransportConfigurationExportRow(
                $transport->getKey(),
                $transport->getTransport(),
                $transport->getTitle(),
                $transport->isEnabled(),
                null !== $vendorConfig ? json_encode($vendorConfig, JSON_THROW_ON_ERROR) : null,
            );
        }
    }
}
