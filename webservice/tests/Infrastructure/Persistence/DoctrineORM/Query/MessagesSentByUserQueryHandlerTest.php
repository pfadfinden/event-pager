<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Persistence\DoctrineORM\Query;

use App\Core\SendMessage\Model\IncomingMessage;
use App\Core\SendMessage\Query\MessageFilter;
use App\Core\SendMessage\Query\MessagesSentByUser;
use App\Core\SendMessage\ReadModel\IncomingMessageStatus;
use App\Infrastructure\Persistence\DoctrineORM\Query\MessagesSentByUserQueryHandler;
use Brick\DateTime\Clock\FixedClock;
use Brick\DateTime\DefaultClock;
use Brick\DateTime\Instant;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Ulid;
use Zenstruck\Foundry\Test\ResetDatabase;

#[Group('integration'), Group('integration.database')]
#[CoversClass(MessagesSentByUser::class)]
#[CoversClass(MessagesSentByUserQueryHandler::class)]
final class MessagesSentByUserQueryHandlerTest extends KernelTestCase
{
    use ResetDatabase;

    public function testRetrievesMessageFromDatabase(): void
    {
        // Arrange
        $message1 = $this->testMessage();
        $message2 = $this->testMessage();

        self::bootKernel();
        $container = self::getContainer();
        $em = $container->get(EntityManagerInterface::class);

        // Create fixtures / can be rewritten to use fixture library once established
        $em->persist($message1);
        $em->persist($message2);
        $em->flush();

        $sut = new MessagesSentByUserQueryHandler($em);

        $query = new MessagesSentByUser($message1->by->toString(), new MessageFilter());

        // Act
        /** @var IncomingMessageStatus[] $result */
        $result = iterator_to_array($sut->__invoke($query));

        // Assert
        self::assertCount(1, $result);
        self::assertInstanceOf(IncomingMessageStatus::class, $result[0]);
        self::assertEquals($message1->messageId, $result[0]->messageId);
        self::assertSame($message1->content, $result[0]->content);
        self::assertSame('Unknown', $result[0]->status);

        // Cleanup
        $em->remove($message1);
        $em->remove($message2);
        $em->flush();
    }

    public function testRetrievesMessageFromDatabaseWithFilter(): void
    {
        // Arrange
        $sendBy = Ulid::generate();
        $message1 = $this->testMessage($sendBy);
        $message2 = $this->testMessage($sendBy);
        $message3 = $this->testMessage($sendBy);

        self::bootKernel();
        $container = self::getContainer();
        $em = $container->get(EntityManagerInterface::class);

        // Create fixtures / can be rewritten to use fixture library once established
        $em->persist($message1);
        $em->persist($message2);
        $em->persist($message3);
        $em->flush();

        $sut = new MessagesSentByUserQueryHandler($em);

        $query = new MessagesSentByUser($sendBy, new MessageFilter(offset: 1, limit: 1));

        // Act
        /** @var IncomingMessageStatus[] $result */
        $result = iterator_to_array($sut->__invoke($query));

        // Assert
        self::assertCount(1, $result);
        self::assertInstanceOf(IncomingMessageStatus::class, $result[0]);
        self::assertEquals($message2->messageId, $result[0]->messageId);
        self::assertSame($message2->content, $result[0]->content);
        self::assertSame('Unknown', $result[0]->status);

        // Cleanup
        $em->remove($message1);
        $em->remove($message2);
        $em->flush();
    }

    private function testMessage(?string $sendBy = null): IncomingMessage
    {
        DefaultClock::set(new FixedClock(Instant::of(1_000_000_000)));
        $message = IncomingMessage::new(
            Ulid::fromString($sendBy ?? Ulid::generate()),
            [Ulid::fromString(Ulid::generate()), Ulid::fromString(Ulid::generate())],
            'Hello World',
            1
        );
        DefaultClock::reset();

        return $message;
    }
}
