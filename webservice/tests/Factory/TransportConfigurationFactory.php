<?php

declare(strict_types=1);

namespace App\Tests\Factory;

use App\Core\IntelPage\Application\IntelPageTransport;
use App\Core\NtfyTransport\Application\NtfyTransport;
use App\Core\TelegramTransport\Application\TelegramTransport;
use App\Core\TransportManager\Model\TransportConfiguration;
use Override;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;
use function is_array;

/**
 * @extends PersistentObjectFactory<TransportConfiguration>
 */
final class TransportConfigurationFactory extends PersistentObjectFactory
{
    /** @var list<class-string> */
    private const array TRANSPORT_CLASSES = [
        IntelPageTransport::class,
        TelegramTransport::class,
        NtfyTransport::class,
    ];

    public static function class(): string
    {
        return TransportConfiguration::class;
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        /** @var class-string $transport */
        $transport = self::faker()->randomElement(self::TRANSPORT_CLASSES);

        return [
            'key' => self::faker()->unique()->slug(2),
            'transport' => $transport,
            'title' => self::faker()->words(3, true),
        ];
    }

    #[Override]
    protected function initialize(): static
    {
        return $this->afterInstantiate(function (TransportConfiguration $config, array $attributes): void {
            // Set enabled status if provided
            if (isset($attributes['enabled'])) {
                $config->setEnabled((bool) $attributes['enabled']);
            }

            // Set vendor config if provided
            if (isset($attributes['vendorSpecificConfig']) && is_array($attributes['vendorSpecificConfig'])) {
                $config->setVendorSpecificConfig($attributes['vendorSpecificConfig']);
            }
        });
    }

    public function withKey(string $key): static
    {
        return $this->with(['key' => $key]);
    }

    public function withTitle(string $title): static
    {
        return $this->with(['title' => $title]);
    }

    /**
     * @param class-string $transport
     */
    public function withTransport(string $transport): static
    {
        return $this->with(['transport' => $transport]);
    }

    public function enabled(): static
    {
        return $this->with(['enabled' => true]);
    }

    public function disabled(): static
    {
        return $this->with(['enabled' => false]);
    }

    /**
     * @param array<string, mixed> $config
     */
    public function withVendorConfig(array $config): static
    {
        return $this->with(['vendorSpecificConfig' => $config]);
    }

    public function forIntelPage(): static
    {
        return $this->withTransport(IntelPageTransport::class);
    }

    public function forTelegram(): static
    {
        return $this->withTransport(TelegramTransport::class);
    }

    public function forNtfy(): static
    {
        return $this->withTransport(NtfyTransport::class);
    }
}
