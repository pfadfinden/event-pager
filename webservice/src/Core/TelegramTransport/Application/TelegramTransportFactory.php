<?php

declare(strict_types=1);

namespace App\Core\TelegramTransport\Application;

use App\Core\Contracts\Bus\EventBus;
use App\Core\TelegramTransport\Port\TelegramClientInterface;
use App\Core\TransportContract\Model\SystemTransportConfig;
use App\Core\TransportContract\Port\TransportFactory;

/**
 * Factory to create TelegramTransport instances with system configuration.
 */
final readonly class TelegramTransportFactory implements TransportFactory
{
    public function __construct(
        private TelegramClientInterface $telegramClient,
        private EventBus $eventBus,
    ) {
    }

    public function supports(string $transportClass): bool
    {
        return TelegramTransport::class === $transportClass;
    }

    public function withSystemConfiguration(SystemTransportConfig $config): TelegramTransport
    {
        return new TelegramTransport(
            $config,
            $this->telegramClient,
            $this->eventBus,
        );
    }
}
