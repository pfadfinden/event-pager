<?php

declare(strict_types=1);

namespace App\Tests\Factory\Story\ScoutDE;

use App\Core\IntelPage\Model\CapCode;
use App\Core\IntelPage\Model\Channel;
use App\Core\IntelPage\Model\Pager;
use App\Core\IntelPage\Model\Slot;
use App\Core\MessageRecipient\Model\AbstractMessageRecipient;
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
#[AsFixture(name: 'bdp-scout-event-pagers', groups: ['bdp-scout-event-sample-de'])]
final class PagerStory extends Story
{
    /** @var list<Pager> */
    private array $pagers = [];

    /** @var array<string, Channel> */
    private array $channels = [];

    private const array CHANNEL_NAMES = [
        'Bereichsleitung',
        'Bundesteam Technik',
        'Bundesteam Sicherheit',
        'Platzsicherheit - Alle im Dienst',
        'Sanität - Alle im Dienst',
        'San Rettung 1 ROT',
        'San Rettung 2 GRÜN',
        'Bundeslagerleitung',
        'Unterlagerleitungen',
        'Unterlagertechnik',
        'Unterlagersicherheitsbeauftragte',
        'Unterlagersanität',
        'Sicherheitsstab',
        'Einsatzleitung',
        'Sicherheit',
        'Sicherheit Nord',
        'Sicherheit Süd',
        'Sanitäter',
        'Erste Hilfe',
        'Logistik',
        'Transport',
        'Aufbau',
        'Abbau',
        'Technik',
        'Bühne',
        'Licht & Ton',
        'Strom',
        'Programm',
        'Catering',
        'Küche',
        'Service',
        'Einlass',
        'Kasse',
        'Garderobe',
        'VIP',
        'Backstage',
        'Künstler',
        'Presse',
        'Info',
        'Parkplatz',
        'Shuttle',
        'Reinigung',
        'Notfall',
        'Funk 1',
        'Funk 2',
        'Funk 3',
        'Nachtschicht',
        'Koordination',
        'Crowd Control',
        'Lost & Found',
        'Frühschicht',
        'Spätschicht',
        'Reserve 1',
        'Reserve 2',
        'Reserve 3',
        'Test',
        'Wartung',
        'Hauptbühne',
        'Nebenbühne',
        'Außengelände',
        'Innenbereich',
    ];

    private Channel $channelAllSilent;

    private Channel $channelAllLoud;

    public function build(): void
    {
        $this->buildChannels();
        $this->buildChannelTransportConfigOnGroups();
        $this->buildPager();
    }

    public function buildChannels(): void
    {
        $this->channelAllLoud = PagerChannelFactory::createOne([
            'name' => 'Alle',
            'capCode' => CapCode::fromInt(1001),
            'audible' => true,
            'vibration' => true,
        ]);
        $this->channelAllSilent = PagerChannelFactory::createOne([
            'name' => 'Alle - Leise',
            'capCode' => CapCode::fromInt(1009),
            'audible' => false,
            'vibration' => true,
        ]);
        $capCode = 1001 + 8 + 8;
        foreach (self::CHANNEL_NAMES as $name) {
            $this->channels[$name] = PagerChannelFactory::createOne([
                'name' => $name,
                'capCode' => CapCode::fromInt($capCode++),
                'audible' => true,
                'vibration' => true,
            ]);
        }

        $this->addState('channels', $this->channels);
    }

    public function buildChannelTransportConfigOnGroups(): void
    {
        /** @var list<AbstractMessageRecipient> $groups */
        $groups = EventTeamStory::getPool('groups');
        foreach ($groups as $group) {
            /** @var Channel $channel */
            foreach ([...$this->channels, $this->channelAllSilent, $this->channelAllLoud] as $channel) {
                $channelName = $channel->getName();
                if (null !== $channelName && str_starts_with($channelName, $group->getName())) {
                    $config = $group->addTransportConfiguration('default-pager');
                    $config->setVendorSpecificConfig(['channel' => $channel->getId()]);
                    save($group);
                    break;
                }
            }
        }
    }

    public function buildPager(): void
    {
        $roles = EventTeamStory::getPool('roles');
        $capCodeBase = 8001;
        $maxPager = 50;

        for ($pagerNumber = 1; $pagerNumber < $maxPager; ++$pagerNumber) {
            $label = 'Pager '.$pagerNumber;
            $carriedBy = null;
            if (isset($roles[$pagerNumber - 1])) {
                $role = $roles[$pagerNumber - 1];
                /** @var AbstractMessageRecipient $carriedBy */
                $carriedBy = $pagerNumber < 3 ? $role->person : $role;
                $carriedBy->addTransportConfiguration('default-pager');
                save($carriedBy);
                $label = $carriedBy->getName();
            }

            $pager = PagerFactory::createOne([
                'label' => $label,
                'number' => $pagerNumber,
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
                ->assignChannel(Slot::fromInt(3), $this->channelAllSilent);

            $slot = 4;

            foreach (($carriedBy?->getGroups() ?? []) as $group) {
                $channelName = array_find(self::CHANNEL_NAMES, fn (string $n): bool => str_starts_with($n, $group->getName()));
                if (null !== $channelName && isset($this->channels[$channelName])) {
                    $pager->assignChannel(Slot::fromInt($slot++), $this->channels[$channelName]);
                }
            }

            $pager->setCarriedBy($carriedBy);

            // Activate most pagers, leave some as reserves
            $pager->setActivated(1 !== random_int(0, 4)); // 80% activated

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
