<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Persistence\DoctrineORM\Query;

use App\Core\MessageRecipient\Model\Person;
use App\Core\UserManagement\Model\User;
use App\Core\UserManagement\Query\UserById;
use App\Infrastructure\Persistence\DoctrineORM\Query\UserByIdQueryHandler;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

#[CoversClass(UserById::class)]
#[CoversClass(UserByIdQueryHandler::class)]
#[Group('integration')]
final class UserByIdQueryHandlerTest extends KernelTestCase
{
    use ResetDatabase;

    public function testReturnsNullRecipientIdWhenUserHasNoLinkedRecipient(): void
    {
        self::bootKernel();
        $em = self::getContainer()->get(EntityManagerInterface::class);

        $user = new User('unlinked-user');
        $em->persist($user);
        $em->flush();

        $sut = new UserByIdQueryHandler($em);
        $result = $sut->__invoke(UserById::withId($user->getId()->toString()));

        self::assertNotNull($result);
        self::assertNull($result->recipientId);
    }

    public function testReturnsRecipientIdWhenUserHasLinkedRecipient(): void
    {
        self::bootKernel();
        $em = self::getContainer()->get(EntityManagerInterface::class);

        $person = new Person('Linked Person');
        $em->persist($person);

        $user = new User('linked-user');
        $user->setRecipient($person);
        $em->persist($user);
        $em->flush();

        $sut = new UserByIdQueryHandler($em);
        $result = $sut->__invoke(UserById::withId($user->getId()->toString()));

        self::assertNotNull($result);
        self::assertSame($person->getId()->toString(), $result->recipientId);
    }
}
