<?php

declare(strict_types=1);

namespace App\Tests\Factory\Story\ScoutDE;

use App\Core\MessageRecipient\Model\Group;
use App\Core\MessageRecipient\Model\Person;
use App\Tests\Factory\RecipientGroupFactory;
use App\Tests\Factory\RecipientPersonFactory;
use App\Tests\Factory\RecipientRoleFactory;
use Zenstruck\Foundry\Attribute\AsFixture;
use Zenstruck\Foundry\Story;
use function count;
use function in_array;
use function random_int;
use function Zenstruck\Foundry\Persistence\refresh;
use function Zenstruck\Foundry\Persistence\save;

/**
 * Creates a realistic event team structure for a large scale scouting event. Language is German.
 * Also loads PagerStory and assigns pagers;.
 *
 * Use: EventTeamStory::load();
 */
#[AsFixture(name: 'bdp-scout-event-sample-de-recipients', groups: ['bdp-scout-event-sample-de'])]
final class EventTeamStory extends Story
{
    public const array ROLES = [
        'Bundeslagerleitung On Duty' => ['Sicherheitsstab'],
        'Bereichsleitung Sicherheit On Duty' => ['Sicherheitsstab'],
        'Bereichsleitung Technik On Duty' => ['Sicherheitsstab'],
        'Technikzentrale' => [],
        'Sicherheitszentrale' => [],
        'Platzsicherheit 1' => [],
        'Platzsicherheit 2' => [],
        'Platzsicherheit Rufbereitschaft' => [],
        'Notarzt' => ['Sanität - Alle im Dienst'],
        'San Rettung 1-1 ROT' => ['San Rettung 1 ROT'],
        'San Rettung 1-2 ROT' => ['San Rettung 1 ROT'],
        'San Rettung 2-1 GRÜN' => ['San Rettung 2 GRÜN'],
        'San Rettung 2-2 GRÜN' => ['San Rettung 2 GRÜN'],
        'San Triage' => ['Sanität - Alle im Dienst'],
        'Sicherheitsstab - S4' => ['Sicherheitsstab'],
        'Fachberatung Presse' => ['Sicherheitsstab - Fachberatungen'],
        'Fachberatung Feuerwehr' => ['Sicherheitsstab - Fachberatungen'],
        'Bundesvorstand On Duty' => ['Sicherheitsstab'],
        'Bundesamt On Duty' => ['Sicherheitsstab'],
        'Ärtztliche Leitung' => ['Sicherheitsstab - Fachberatungen'],
        'Führungsunterstützung' => ['Sicherheitsstab'],
        'UL Sicherheitsbeauftragter SH-HH' => ['Unterlagersicherheitsbeauftragte'],
        'UL Sicherheitsbeauftragter NDS' => ['Unterlagersicherheitsbeauftragte'],
        'UL Sicherheitsbeauftragter BBB' => ['Unterlagersicherheitsbeauftragte'],
        'UL Sicherheitsbeauftragter NRW' => ['Unterlagersicherheitsbeauftragte'],
        'UL Sicherheitsbeauftragter HE' => ['Unterlagersicherheitsbeauftragte'],
        'UL Sicherheitsbeauftragter RPS' => ['Unterlagersicherheitsbeauftragte'],
        'UL Sicherheitsbeauftragter SXN' => ['Unterlagersicherheitsbeauftragte'],
        'UL Sicherheitsbeauftragter BaWü' => ['Unterlagersicherheitsbeauftragte'],
        'UL Sicherheitsbeauftragter BY' => ['Unterlagersicherheitsbeauftragte'],
        'Fahrdienst 1' => [],
        'Fahrdienst 2' => [],
        'Fahrdienst 3' => [],
        'Feuerwehr 1' => [],
        'Feuerwehr 2' => [],
        'Feuerwehr 3' => [],
        'Feuerwehr 4' => [],
        'Feuerwehr 5' => [],
    ];

    private const array GROUPS = [
        'Alle' => [],
        'Komitee' => [],
        'Bereichsleitungen' => ['Komitee'],
        'Bereichsleitung Sicherheit' => ['Bereichsleitungen'],
        'Bereichsleitung Technik' => ['Bereichsleitungen'],
        'Platzsicherheit - Alle im Dienst' => [],
        'Sanität - Alle im Dienst' => [],
        'San Rettung 1 ROT' => [],
        'San Rettung 2 GRÜN' => [],
        'Bundeslagerleitung' => ['Komitee'],
        'Unterlagerleitungen' => [],
        'Unterlagertechnik' => [],
        'Unterlagersicherheitsbeauftragte' => [],
        'Unterlagersanität' => [],
        'Sicherheitsstab' => [],
        'Sicherheitsstab - Fachberatungen' => [],
    ];

    private const array GROUPS_WITH_DIRECT_MEMBERS = [
        'Bundesteam Sicherheit' => [],
        'Bundesteam Technik' => [],
        'Bereichsleitung Programm' => ['Bereichsleitungen'],
        'Bereichsleitung Intakt' => ['Bereichsleitungen'],
    ];

    /** @var array<string, Group> */
    private array $groups = [];

    /** @var list<Person> */
    private array $persons = [];

    public function build(): void
    {
        $this->createPersons();
        $this->createGroups();
        $this->createRoles();
        $this->assignPersonsToGroups();

        // Load dependent stories first
        PagerStory::load();
    }

    private function createPersons(): void
    {
        // Create 200 persons
        $this->persons =
            RecipientPersonFactory::new()
                ->many(200)
                ->applyStateMethod('withGermanName')
                ->create();
    }

    private function createGroups(): void
    {
        foreach ([...self::GROUPS, ...self::GROUPS_WITH_DIRECT_MEMBERS] as $groupName => $parents) {
            $group = RecipientGroupFactory::createOne(['name' => $groupName]);
            $this->groups[$groupName] = $group;

            // Add to parent groups
            foreach ($parents as $parent) {
                if (isset($this->groups[$parent])) {
                    $this->groups[$parent]->addMember($group);
                }
            }

            $this->addToPool('groups', $group);
        }
    }

    private function createRoles(): void
    {
        $personIndex = 0;
        // Create leadership roles for each department
        foreach (self::ROLES as $roleName => $parents) {
            $role = RecipientRoleFactory::createOne([
                'name' => $roleName,
                'person' => $this->persons[$personIndex++],
            ]);
            // Add to parent groups
            foreach ($parents as $parent) {
                if (isset($this->groups[$parent])) {
                    $this->groups[$parent]->addMember($role);
                }
            }
            save($role);
            flush();
            refresh($role);
            $this->addToPool('roles', $role);
        }
    }

    private function assignPersonsToGroups(): void
    {
        $groupCount = count(self::GROUPS_WITH_DIRECT_MEMBERS);

        foreach ($this->persons as $person) {
            $this->groups['Alle']->addMember($person);

            // Assign each person to 1-3 groups randomly
            $numGroups = random_int(1, 3);
            $assignedGroups = [];

            for ($j = 0; $j < $numGroups; ++$j) {
                $randomGroupIndex = random_int(0, $groupCount - 1);
                $groupName = array_keys(self::GROUPS_WITH_DIRECT_MEMBERS)[$randomGroupIndex];
                $group = $this->groups[$groupName];

                if (!in_array($group, $assignedGroups, true)) {
                    $group->addMember($person);
                    save($group);
                    $assignedGroups[] = $group;
                }
            }
        }
        save($this->groups['Alle']);
    }
}
