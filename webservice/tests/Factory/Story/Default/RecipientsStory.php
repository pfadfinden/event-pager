<?php

declare(strict_types=1);

namespace App\Tests\Factory\Story\Default;

use App\Core\MessageRecipient\Model\Group;
use App\Core\MessageRecipient\Model\Person;
use App\Core\MessageRecipient\Model\Role;
use App\Tests\Factory\RecipientGroupFactory;
use App\Tests\Factory\RecipientPersonFactory;
use App\Tests\Factory\RecipientRoleFactory;
use Zenstruck\Foundry\Attribute\AsFixture;
use Zenstruck\Foundry\Story;
use function array_slice;
use function array_values;
use function count;
use function in_array;
use function random_int;
use function Zenstruck\Foundry\Persistence\save;

/**
 * Creates a realistic event team structure with:
 * - 200 Persons
 * - 100 Groups
 * - 100 Roles assigned to persons
 *
 * Use: EventTeamStory::load();
 */
#[AsFixture(name: 'default-recipients', groups: ['default'])]
final class RecipientsStory extends Story
{
    /** @var list<Group> */
    private array $topLevelGroups = [];

    /** @var list<Group> */
    private array $secondLevelGroups = [];

    /** @var list<Person> */
    private array $persons = [];

    /** @var list<Role> */
    private array $roles = [];

    public function build(): void
    {
        $this->createPersons();
        $this->createGroups();
        $this->createRoles();
        $this->assignPersonsToGroups();
    }

    private function createPersons(): void
    {
        // Create 200 persons
        $this->persons = RecipientPersonFactory::createMany(200);
    }

    private function createGroups(): void
    {
        $this->topLevelGroups = RecipientGroupFactory::createMany(100);

        foreach ($this->topLevelGroups as $topLevelGroup) {
            $secondLevelGroup = RecipientGroupFactory::createOne();
            $topLevelGroup->addMember($secondLevelGroup);
            $this->secondLevelGroups[] = $secondLevelGroup;
        }
    }

    private function createRoles(): void
    {
        $personIndex = 0;

        // Create team leader roles for teams
        $teamRoleCount = 0;
        foreach ([...array_slice($this->secondLevelGroups, 50), ...$this->topLevelGroups] as $group) {
            if ($teamRoleCount >= 100) {
                break;
            } // Limit to stay within 100 roles

            // Team leader
            if ($personIndex < count($this->persons)) {
                $role = RecipientRoleFactory::createOne([
                    'name' => 'Teamlead '.$group->getName(),
                    'person' => $this->persons[$personIndex++],
                ]);
                $this->roles[] = $role;
                $group->addMember($role);
                save($group);
                ++$teamRoleCount;
            }
        }

        $this->addState('roles', $this->roles);
    }

    private function assignPersonsToGroups(): void
    {
        // Get all team groups as array
        $allTeamGroups = array_values($this->secondLevelGroups);
        $personCount = count($this->persons);
        $groupCount = count($allTeamGroups);

        if (0 === $groupCount) {
            return;
        }

        // Assign remaining persons (not already assigned to roles) to teams
        // Skip first ~100 persons as they're likely role holders
        $startIndex = 100;

        for ($i = $startIndex; $i < $personCount; ++$i) {
            $person = $this->persons[$i];

            // Assign each person to 1-3 groups randomly
            $numGroups = random_int(1, 3);
            $assignedGroups = [];

            for ($j = 0; $j < $numGroups; ++$j) {
                $randomGroupIndex = random_int(0, $groupCount - 1);
                $group = $allTeamGroups[$randomGroupIndex];

                if (!in_array($group, $assignedGroups, true)) {
                    $group->addMember($person);
                    save($group);
                    $assignedGroups[] = $group;
                }
            }
        }
    }
}
