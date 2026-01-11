<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Persistence\DoctrineORM\Repository;

use App\Core\MessageRecipient\Model\AbstractMessageRecipient;
use App\Core\MessageRecipient\Model\Role;
use App\Infrastructure\Persistence\DoctrineORM\Repository\DoctrineMessageRecipientRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

#[Group('integration'), Group('integration.database')]
#[CoversClass(DoctrineMessageRecipientRepository::class)]
final class DoctrineMessageRecipientRepositoryTest extends KernelTestCase
{
    use ResetDatabase;

    public function testAddNewRole(): void
    {
        // Arrange
        self::bootKernel();
        $container = static::getContainer();
        $em = $container->get(EntityManagerInterface::class);

        $sut = new DoctrineMessageRecipientRepository($em);

        $recipient = new Role('Role A', null);

        // Act
        $sut->add($recipient);
        $em->flush(); // in real code: use UOW to commit transaction

        // Assert
        $em->clear();
        $result = $em->find(AbstractMessageRecipient::class, $recipient->getId());

        self::assertInstanceOf(Role::class, $result);
        self::assertEquals('Role A', $result->getName());
    }
}
