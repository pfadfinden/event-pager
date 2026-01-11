<?php

declare(strict_types=1);

namespace App\Core\NtfyTransport\Application;

use App\Core\Contracts\Bus\EventBus;
use App\Core\NtfyTransport\Port\NtfyClientInterface;
use App\Core\TransportContract\Model\SystemTransportConfig;
use App\Core\TransportContract\Port\TransportFactory;

/**
 * Factory to create NtfyTransport instances with system configuration.
 */
final readonly class NtfyTransportFactory implements TransportFactory
{
    public function __construct(
        private NtfyClientInterface $ntfyClient,
        private EventBus $eventBus,
    ) {
    }

    public function supports(string $transportClass): bool
    {
        return NtfyTransport::class === $transportClass;
    }

    public function withSystemConfiguration(SystemTransportConfig $config): NtfyTransport
    {
        return new NtfyTransport(
            $config,
            $this->ntfyClient,
            $this->eventBus,
        );
    }
}
