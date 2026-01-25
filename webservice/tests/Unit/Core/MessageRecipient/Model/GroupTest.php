<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\MessageRecipient\Model;

use App\Core\MessageRecipient\Model\Group;
use App\Core\MessageRecipient\Model\MessageRecipient;
use App\Core\MessageRecipient\Model\Person;
use InvalidArgumentException;
use Iterator;
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
     * @return Iterator<(int | string), array{string, (Ulid | null)}>
     */
    public static function constructorProvider(): Iterator
    {
        yield ['Simple Group', null];
        yield ['Another Group', new Ulid()];
    }

    #[DataProvider('constructorProvider')]
    public function testConstructor(string $name, ?Ulid $id): void
    {
        $group = new Group($name, $id);

        if (!$id instanceof Ulid) {
            $group->getId()->toString();
        } else {
            self::assertSame($id, $group->getId());
        }
        self::assertSame($name, $group->getName());
        self::assertSame([], $group->getMembers());
    }

    /**
     * @return Iterator<(int | string), array{bool, (Person | null)}>
     */
    public static function canResolveProvider(): Iterator
    {
        yield [true, new Person('Bob')];
        yield [false, null];
    }

    #[DataProvider('canResolveProvider')]
    public function testCanResolve(bool $expected, ?Person $person): void
    {
        $group = new Group('Important', Ulid::fromString(Ulid::generate()));

        if ($person instanceof Person) {
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

        $members = array_map(fn (MessageRecipient $r): string => $r->getName(), iterator_to_array($group1->getMembersRecursively(), false));

        self::assertSame(['Eve', 'Maria', 'Peter', 'Adam', 'Clair'], $members);
    }
}
