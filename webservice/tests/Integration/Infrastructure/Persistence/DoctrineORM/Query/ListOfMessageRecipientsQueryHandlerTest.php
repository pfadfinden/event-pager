<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Persistence\DoctrineORM\Query;

use App\Core\MessageRecipient\Model\Person;
use App\Core\MessageRecipient\Model\Role;
use App\Core\MessageRecipient\Query\ListOfMessageRecipients;
use App\Core\MessageRecipient\ReadModel\RecipientListEntry;
use App\Infrastructure\Persistence\DoctrineORM\Query\ListOfMessageRecipientsQueryHandler;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

#[CoversClass(ListOfMessageRecipients::class)]
#[CoversClass(ListOfMessageRecipientsQueryHandler::class)]
final class ListOfMessageRecipientsQueryHandlerTest extends KernelTestCase
{
    use ResetDatabase;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = self::getContainer();
        $em = $container->get(EntityManagerInterface::class);
        $em->persist(new \App\Core\MessageRecipient\Model\Group('List Test Group'));
        $em->persist(new Role('List Test Role', null));
        $em->persist(new Role('List Test Role', null));
        $em->persist(new Person('List Test Person'));
        $em->persist(new Person('List Test Person'));
        $em->persist(new Person('List Test Person'));
        $em->flush();
        $em->clear();
    }

    public function testCanRetrieveListOfAll(): void
    {
        // Arrange
        $container = self::getContainer();
        $em = $container->get(EntityManagerInterface::class);

        $sut = new ListOfMessageRecipientsQueryHandler($em);

        $query = ListOfMessageRecipients::all();

        // Act
        $result = iterator_to_array($sut->__invoke($query));

        // Assert
        self::assertCount(6, $result);
        self::assertContainsOnlyInstancesOf(RecipientListEntry::class, $result);
    }

    public function testCanRetrieveListOfRoles(): void
    {
        // Arrange
        $container = self::getContainer();
        $em = $container->get(EntityManagerInterface::class);

        $sut = new ListOfMessageRecipientsQueryHandler($em);

        $query = ListOfMessageRecipients::onlyRoles();

        // Act
        $result = iterator_to_array($sut->__invoke($query));

        // Assert
        self::assertCount(2, $result);
        self::assertContainsOnlyInstancesOf(RecipientListEntry::class, $result);
        self::assertSame('ROLE', $result[0]->type);
    }

    public function testCanRetrieveListOfGroups(): void
    {
        // Arrange
        $container = self::getContainer();
        $em = $container->get(EntityManagerInterface::class);

        $sut = new ListOfMessageRecipientsQueryHandler($em);

        $query = ListOfMessageRecipients::onlyGroups();

        // Act
        $result = iterator_to_array($sut->__invoke($query));

        // Assert
        self::assertCount(1, $result);
        self::assertContainsOnlyInstancesOf(RecipientListEntry::class, $result);
        self::assertSame('GROUP', $result[0]->type);
        self::assertSame('List Test Group', $result[0]->name);
    }

    public function testCanRetrieveListOfPeople(): void
    {
        // Arrange
        $container = self::getContainer();
        $em = $container->get(EntityManagerInterface::class);

        $sut = new ListOfMessageRecipientsQueryHandler($em);

        $query = ListOfMessageRecipients::onlyPeople();

        // Act
        $result = iterator_to_array($sut->__invoke($query));

        // Assert
        self::assertCount(3, $result);
        self::assertContainsOnlyInstancesOf(RecipientListEntry::class, $result);
        self::assertSame('PERSON', $result[0]->type);
    }
}
