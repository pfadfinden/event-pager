<?php

declare(strict_types=1);

namespace App\Tests\Factory;

use App\Core\MessageRecipient\Model\Group;
use Override;
use Symfony\Component\Uid\Ulid;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Group>
 */
final class RecipientGroupFactory extends PersistentObjectFactory
{
    private const array EVENT_TEAM_PREFIXES = [
        'Team', 'Group', 'Area', 'Department', 'Unit', 'Squad',
    ];

    private const array EVENT_TEAM_NAMES = [
        'Logistics', 'Security', 'Medics', 'Program', 'Technical', 'Catering',
        'Admission', 'Artist Support', 'VIP Service', 'Communications', 'Setup',
        'Teardown', 'Parking', 'Stewards', 'Stage', 'Lighting', 'Sound', 'Decoration',
        'Cloakroom', 'Cash Desk', 'Information', 'First Aid', 'Fire Safety',
        'Shuttle', 'Transport', 'Cleaning', 'Waste Disposal', 'Water Supply',
        'Power Supply', 'Night Shift', 'Day Shift', 'Weekend', 'Main Stage',
        'Side Stage', 'Backstage', 'Press', 'Marketing', 'Social Media', 'Photo',
        'Video', 'Documentation', 'Volunteers', 'Helpers', 'Management', 'Coordination',
        'Emergency', 'Evacuation', 'Crowd Management', 'Visitor Service', 'Lost & Found',
    ];

    private const array LOCATION_SUFFIXES = [
        'North', 'South', 'East', 'West', 'Center', 'A', 'B', 'C', 'D', '1', '2', '3',
        'Main Entrance', 'Side Entrance', 'Hall 1', 'Hall 2', 'Outdoor Area', 'Indoor Area',
    ];

    public static function class(): string
    {
        return Group::class;
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        $usePrefix = self::faker()->boolean(70);
        $useSuffix = self::faker()->boolean(30);

        $name = '';
        if ($usePrefix) {
            /** @var string $prefix */
            $prefix = self::faker()->randomElement(self::EVENT_TEAM_PREFIXES);
            $name .= $prefix.' ';
        }
        /** @var string $teamName */
        $teamName = self::faker()->randomElement(self::EVENT_TEAM_NAMES);
        $name .= $teamName;
        if ($useSuffix) {
            /** @var string $suffix */
            $suffix = self::faker()->randomElement(self::LOCATION_SUFFIXES);
            $name .= ' '.$suffix;
        }

        return [
            'name' => $name,
            'id' => new Ulid(),
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
}
