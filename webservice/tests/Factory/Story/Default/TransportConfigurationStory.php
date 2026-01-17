<?php

declare(strict_types=1);

namespace App\Tests\Factory\Story\Default;

use App\Core\IntelPage\Application\IntelPageTransport;
use App\Core\TelegramTransport\Application\TelegramTransport;
use App\Tests\Factory\TransportConfigurationFactory;
use Zenstruck\Foundry\Attribute\AsFixture;
use Zenstruck\Foundry\Story;

/**
 * Creates one of each of the potential transports.
 */
#[AsFixture(name: 'default-transports', groups: ['default', 'bdp-scout-event-sample-de'])]
final class TransportConfigurationStory extends Story
{
    public function build(): void
    {
        // Default pager transport
        TransportConfigurationFactory::createOne([
            'key' => 'default-pager',
            'transport' => IntelPageTransport::class,
            'title' => 'Pager - Default',
            'enabled' => true,
        ]);

        // NTFY transport connected to local docker compose container
        TransportConfigurationFactory::createOne([
            'key' => 'ntfy-dev',
            'transport' => TelegramTransport::class,
            'title' => 'Notify Local',
            'enabled' => true,
            'vendorSpecificConfig' => [
                'serverUrl' => 'ntfy:80',
            ],
        ]);

        // Telegram transport, enabled only if bot token was provided
        TransportConfigurationFactory::createOne([
            'key' => 'telegram-dev',
            'transport' => TelegramTransport::class,
            'title' => 'Telegram Bot',
            'enabled' => '' !== getenv('TELEGRAM_BOT_TOKEN') && false !== getenv('TELEGRAM_BOT_TOKEN'),
            'vendorSpecificConfig' => [
                'botToken' => getenv('TELEGRAM_BOT_TOKEN'),
            ],
        ]);
    }
}
