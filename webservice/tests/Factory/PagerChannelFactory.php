<?php

declare(strict_types=1);

namespace App\Tests\Factory;

use App\Core\IntelPage\Model\CapCode;
use App\Core\IntelPage\Model\Channel;
use App\Core\IntelPage\Model\Pager;
use App\Core\IntelPage\Model\Slot;
use Override;
use Symfony\Component\Uid\Ulid;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Channel>
 */
final class PagerChannelFactory extends PersistentObjectFactory
{
    /** @phpstan-ignore property.readOnlyByPhpDocDefaultValue */
    private static int $capCodeCounter = 1000;

    public static function class(): string
    {
        return Channel::class;
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        $capCode = self::$capCodeCounter++;
        if ($capCode > 9999) {
            $capCode = self::faker()->unique()->numberBetween(1000, 7999);
        }

        $name = self::faker()->unique()->word();

        return [
            'id' => new Ulid(),
            'name' => $name,
            'capCode' => CapCode::fromInt($capCode),
            'audible' => self::faker()->boolean(90),
            'vibration' => self::faker()->boolean(95),
        ];
    }

    #[Override]
    protected function initialize(): static
    {
        return $this;
    }

    public function withName(string $name): static
    {
        return $this->with(['name' => $name]);
    }

    public function withCapCode(int $capCode): static
    {
        return $this->with(['capCode' => CapCode::fromInt($capCode)]);
    }

    public function silent(): static
    {
        return $this->with(['audible' => false, 'vibration' => true]);
    }

    public function loud(): static
    {
        return $this->with(['audible' => true, 'vibration' => true]);
    }

    /**
     * @param Channel[] $channels
     */
    public static function assignRandomChannelsToPager(Pager $pager, int $startSlot, int $maxSlot, array $channels): Pager
    {
        $lastSlotToFill = self::faker()->numberBetween($startSlot, $maxSlot);
        for ($slot = $startSlot; $slot <= $lastSlotToFill - 1; ++$slot) {
            /** @var Channel $channel */
            $channel = self::faker()->randomElement($channels);
            $pager->assignChannel(Slot::fromInt($slot++), $channel);
        }

        return $pager;
    }
}
