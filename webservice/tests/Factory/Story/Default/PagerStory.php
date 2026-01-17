<?php

declare(strict_types=1);

namespace App\Tests\Factory\Story\Default;

use App\Core\IntelPage\Model\CapCode;
use App\Core\IntelPage\Model\Channel;
use App\Core\IntelPage\Model\Pager;
use App\Core\IntelPage\Model\Slot;
use App\Tests\Factory\PagerChannelFactory;
use App\Tests\Factory\PagerFactory;
use Zenstruck\Foundry\Attribute\AsFixture;
use Zenstruck\Foundry\Story;
use function random_int;
use function Zenstruck\Foundry\Persistence\save;

/**
 * Creates 50 pagers for event communication.
 *
 * Each pager is assigned two individual cap codes:
 * - Slot 0: Audible cap code (alert tone + vibration)
 * - Slot 1: Silent cap code (vibration only)
 *
 * Cap codes are unique per pager, starting from 2001.
 *
 * Use: PagerStory::load();
 */
#[AsFixture(name: 'default-pagers', groups: ['default'])]
final class PagerStory extends Story
{
    /** @var list<Pager> */
    private array $pagers = [];

    /** @var list<Channel> */
    private array $channels = [];

    private Channel $channelAllSilent;

    private Channel $channelAllLoud;

    public function build(): void
    {
        $this->buildChannels();
        $this->buildPager();
    }

    public function buildChannels(): void
    {
        $this->channelAllLoud = PagerChannelFactory::createOne([
            'name' => 'All - Alarm',
            'capCode' => CapCode::fromInt(1001),
            'audible' => true,
            'vibration' => true,
        ]);
        $this->channelAllSilent = PagerChannelFactory::createOne([
            'name' => 'All - Vibration Only',
            'capCode' => CapCode::fromInt(1009),
            'audible' => false,
            'vibration' => true,
        ]);

        $this->channels = PagerChannelFactory::createMany(50);

        $this->addState('channel-all-loud', $this->channelAllLoud);
        $this->addState('channel-all-silent', $this->channelAllSilent);
        $this->addState('channels', $this->channels);
    }

    public function buildPager(): void
    {
        $capCodeBase = 8001;

        for ($pagerNumber = 1; $pagerNumber < 100; ++$pagerNumber) {
            $pager = PagerFactory::createOne([
                'number' => $pagerNumber++,
            ]);

            $pager

                // Assign two individual cap codes per pager
                // Slot 0: Audible (alert) - both audible and vibration
                ->assignIndividualCap(
                    Slot::fromInt(0),
                    CapCode::fromInt($capCodeBase),
                    true,  // audible
                    true   // vibration
                )

                // Slot 1: Silent - vibration only
               ->assignIndividualCap(
                   Slot::fromInt(1),
                   CapCode::fromInt($capCodeBase + 1000),
                   false, // not audible
                   true   // vibration
               )

                ->assignChannel(Slot::fromInt(2), $this->channelAllLoud)
                ->assignChannel(Slot::fromInt(3), $this->channelAllSilent)

                // Activate most pagers, leave some as reserves
                ->setActivated(1 !== random_int(0, 4)); // 80% activated

            // Optionally assign more channels
            PagerChannelFactory::assignRandomChannelsToPager($pager, 4, 8, $this->channels);
            save($pager);

            $capCodeBase += 8;
            $this->pagers[] = $pager;
        }

        $this->addState('pagers', $this->pagers);
    }

    /**
     * @return list<Pager>
     */
    public static function getPagers(): array
    {
        /** @var list<Pager> $pagers */
        $pagers = self::get('pagers');

        return $pagers;
    }
}
