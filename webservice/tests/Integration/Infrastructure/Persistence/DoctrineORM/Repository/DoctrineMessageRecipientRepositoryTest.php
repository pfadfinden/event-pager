<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Persistence\DoctrineORM\Repository;

use App\Core\MessageRecipient\Model\AbstractMessageRecipient;
use App\Core\MessageRecipient\Model\Role;
use App\Infrastructure\Persistence\DoctrineORM\Repository\DoctrineMessageRecipientRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

#[CoversClass(DoctrineMessageRecipientRepository::class)]
final class DoctrineMessageRecipientRepositoryTest extends KernelTestCase
{
    use ResetDatabase;

    public function testAddNewRole(): void
    {
        // Arrange
        self::bootKernel();
        $container = self::getContainer();
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
        self::assertSame('Role A', $result->getName());
    }
}
