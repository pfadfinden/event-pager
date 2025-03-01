<?php

declare(strict_types=1);

namespace App\Tests\Core\MessageRecipient\Model;

use App\Core\MessageRecipient\Model\Group;
use App\Core\MessageRecipient\Model\Person;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[CoversClass(Group::class)]
#[Small()]
final class GroupTest extends TestCase
{
    /**
     * @return array{0: string, 1: ?Ulid}[]
     */
    public static function constructorProvider(): array
    {
        return [
            ['Simple Group', null],
            ['Another Group', new Ulid()],
        ];
    }

    #[DataProvider('constructorProvider')]
    public function testConstructor(string $name, ?Ulid $id): void
    {
        $group = new Group($name, $id);

        if (null === $id) {
            $group->id->toString();
        } else {
            self::assertSame($id, $group->id);
        }
        self::assertSame($name, $group->getName());
        self::assertSame([], $group->getMembers());
    }

    /**
     * @return array{0: bool, 1: ?Person}[]
     */
    public static function canResolveProvider(): array
    {
        return [
            [true, new Person('Bob')],
            [false, null],
        ];
    }

    #[DataProvider('canResolveProvider')]
    public function testCanResolve(bool $expected, ?Person $person): void
    {
        $group = new Group('Important', Ulid::fromString(Ulid::generate()));

        if (null !== $person) {
            $group->addMember($person);
        }
        self::assertSame($expected, $group->canResolve());
    }

    public function testResolve(): void
    {
        $person = new Person('Eve');
        $group = new Group('Single Person Group');
        $group->addMember($person);

        $result = $group->resolve();

        self::assertSame([$person], $result);
    }

    public function testErrorOnAddToSelf(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $group = new Group('One Group');
        $group->addMember($group);
    }

    public function testResolveException(): void
    {
        $group = new Group('Empty Group');

        self::expectException(LogicException::class);

        $group->resolve();
    }

    public function testRemoveMember(): void
    {
        $adam = new Person('Adam');
        $clair = new Person('Clair');
        $eve = new Person('Eve');
        $group = new Group('Changing Group');
        $group->addMember($adam);
        $group->addMember($clair);
        $group->addMember($eve);
        self::assertSame([$adam, $clair, $eve], $group->getMembers());

        $group->removeMember($adam);
        self::assertSame([$clair, $eve], $group->getMembers());

        $group->removeMember($eve);
        self::assertSame([$clair], $group->getMembers());
    }

    public function testRecursiveMembers(): void
    {
        $adam = new Person('Adam');
        $clair = new Person('Clair');
        $eve = new Person('Eve');
        $maria = new Person('Maria');
        $pete = new Person('Peter');
        $group1 = new Group('Changing Group - Outer');
        $group2 = new Group('Changing Group - Inner');
        $group1->addMember($group2);
        $group1->addMember($adam);
        $group1->addMember($clair);
        $group2->addMember($eve);
        $group2->addMember($maria);
        $group2->addMember($pete);

        $members = array_map(fn ($r) => $r->getName(), iterator_to_array($group1->getMembersRecursively(), false));

        self::assertSame(['Eve', 'Maria', 'Peter', 'Adam', 'Clair'], $members);
    }
}
