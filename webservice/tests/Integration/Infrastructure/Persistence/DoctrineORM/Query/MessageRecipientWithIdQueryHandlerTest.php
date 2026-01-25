<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Persistence\DoctrineORM\Query;

use App\Core\MessageRecipient\Query\MessageRecipientWithId;
use App\Infrastructure\Persistence\DoctrineORM\Query\MessageRecipientWithIdQueryHandler;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Ulid;
use Zenstruck\Foundry\Test\ResetDatabase;

#[CoversClass(MessageRecipientWithId::class)]
#[CoversClass(MessageRecipientWithIdQueryHandler::class)]
final class MessageRecipientWithIdQueryHandlerTest extends KernelTestCase
{
    use ResetDatabase;

    public function testCanReturnMessageRecipient(): void
    {
        // Arrange
        $id = Ulid::generate();

        self::bootKernel();
        $container = self::getContainer();
        $em = $container->get(EntityManagerInterface::class);

        // Create fixtures
        $em->persist(new \App\Core\MessageRecipient\Model\Group('Hello World', Ulid::fromString($id)));
        $em->flush();

        $sut = new MessageRecipientWithIdQueryHandler($em);

        $query = new MessageRecipientWithId($id);

        // Act
        $result = $sut->__invoke($query);

        // Assert
        self::assertSame($id, $result->id);
        self::assertSame('Hello World', $result->name);
        self::assertSame('GROUP', $result->type);
    }
}
