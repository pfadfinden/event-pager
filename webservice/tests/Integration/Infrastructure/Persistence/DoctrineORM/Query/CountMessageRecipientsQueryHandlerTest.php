<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Persistence\DoctrineORM\Query;

use App\Core\MessageRecipient\Model\Person;
use App\Core\MessageRecipient\Model\Role;
use App\Core\MessageRecipient\Query\CountOfMessageRecipients;
use App\Infrastructure\Persistence\DoctrineORM\Query\CountMessageRecipientsQueryHandler;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

#[CoversClass(CountOfMessageRecipients::class)]
#[CoversClass(CountMessageRecipientsQueryHandler::class)]
final class CountMessageRecipientsQueryHandlerTest extends KernelTestCase
{
    use ResetDatabase;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = self::getContainer();
        $em = $container->get(EntityManagerInterface::class);
        $em->persist(new \App\Core\MessageRecipient\Model\Group('Count Test Group'));
        $em->persist(new Role('Count Test Role', null));
        $em->persist(new Role('Count Test Role', null));
        $em->persist(new Person('Count Test Person'));
        $em->persist(new Person('Count Test Person'));
        $em->persist(new Person('Count Test Person'));
        $em->flush();
        $em->clear();
    }

    public function testCanRetrieveCountOfAll(): void
    {
        // Arrange
        $container = self::getContainer();
        $em = $container->get(EntityManagerInterface::class);

        $sut = new CountMessageRecipientsQueryHandler($em);

        $query = CountOfMessageRecipients::all();

        // Act
        $result = $sut->__invoke($query);

        // Assert
        self::assertSame(6, $result);
    }

    public function testCanRetrieveCountOfRoles(): void
    {
        // Arrange
        $container = self::getContainer();
        $em = $container->get(EntityManagerInterface::class);

        $sut = new CountMessageRecipientsQueryHandler($em);

        $query = CountOfMessageRecipients::onlyRoles();

        // Act
        $result = $sut->__invoke($query);

        // Assert
        self::assertSame(2, $result);
    }

    public function testCanRetrieveCountOfGroups(): void
    {
        // Arrange
        $container = self::getContainer();
        $em = $container->get(EntityManagerInterface::class);

        $sut = new CountMessageRecipientsQueryHandler($em);

        $query = CountOfMessageRecipients::onlyGroups();

        // Act
        $result = $sut->__invoke($query);

        // Assert
        self::assertSame(1, $result);
    }

    public function testCanRetrieveCountOfPeople(): void
    {
        // Arrange
        $container = self::getContainer();
        $em = $container->get(EntityManagerInterface::class);

        $sut = new CountMessageRecipientsQueryHandler($em);

        $query = CountOfMessageRecipients::onlyPeople();

        // Act
        $result = $sut->__invoke($query);

        // Assert
        self::assertSame(3, $result);
    }
}
