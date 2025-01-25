<?php

declare(strict_types=1);

namespace App\Core\TransportManager\Application;

use App\Core\Contracts\Bus\QueryBus;
use App\Core\TransportContract\Port\Transport;
use App\Core\TransportContract\Port\TransportFactory;
use App\Core\TransportContract\Port\TransportManager;
use App\Core\TransportManager\Model\TransportConfiguration;
use App\Core\TransportManager\Query\AllTransports;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

/**
 * Standard TransportManager application based on user defined TransportationConfiguration objects.
 *
 * @see TransportConfiguration
 */
final readonly class DefaultTransportManager implements TransportManager
{
    /**
     * @param iterable<TransportFactory> $transportFactories
     */
    public function __construct(
        private QueryBus $queryBus,
        #[AutowireIterator('app.transport.factory')]
        private iterable $transportFactories,
    ) {
    }

    public function activeTransports(): iterable
    {
        /** @var iterable<TransportConfiguration> $enabledTransports */
        $enabledTransports = $this->queryBus->get(AllTransports::thatAreEnabled());

        foreach ($enabledTransports as $transportConfig) {
            $transport = $this->instantiate($transportConfig);
            if ($transport instanceof Transport && $transport->acceptsNewMessages()) {
                yield $transport;
            }
        }
    }

    private function instantiate(TransportConfiguration $transportConfig): ?Transport
    {
        foreach ($this->transportFactories as $factory) {
            if ($factory->supports($transportConfig->getTransport())) {
                return $factory->withSystemConfiguration($transportConfig);
            }
        }

        return null;
    }
}
