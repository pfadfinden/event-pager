<?php

declare(strict_types=1);

namespace App\Tests\Core\SendMessage\Model;

use App\Core\SendMessage\Model\IncomingMessage;
use App\Core\SendMessage\Query\MessageFilter;
use App\Core\SendMessage\Query\MessagesSendByUser;
use App\Core\SendMessage\ReadModel\SendMessageStatus;
use App\Infrastructure\Persistence\DoctrineORM\Query\MessagesSendByUserQueryHandler;
use Brick\DateTime\Clock\FixedClock;
use Brick\DateTime\DefaultClock;
use Brick\DateTime\Instant;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Ulid;

#[Group('integration'), Group('integration.database')]
final class MessagesSendByUserQueryHandlerTest extends KernelTestCase
{
    public function testRetrievesMessageFromDatabase(): void
    {
        // Arrange
        $message1 = $this->testMessage();
        $message2 = $this->testMessage();

        self::bootKernel();
        $container = static::getContainer();
        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        // Create fixtures / can be rewritten to use fixture library once established
        $em->persist($message1);
        $em->persist($message2);
        $em->flush();

        $sut = new MessagesSendByUserQueryHandler($em);

        $query = new MessagesSendByUser($message1->sendBy->toString(), new MessageFilter());

        // Act
        /** @var SendMessageStatus[] $result */
        $result = iterator_to_array($sut->__invoke($query));

        // Assert
        self::assertCount(1, $result);
        self::assertInstanceOf(SendMessageStatus::class, $result[0]);
        self::assertEquals($message1->messageId, $result[0]->messageId);
        self::assertEquals($message1->content, $result[0]->content);
        self::assertEquals('Unknown', $result[0]->status);

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
        $container = static::getContainer();
        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        // Create fixtures / can be rewritten to use fixture library once established
        $em->persist($message1);
        $em->persist($message2);
        $em->persist($message3);
        $em->flush();

        $sut = new MessagesSendByUserQueryHandler($em);

        $query = new MessagesSendByUser($sendBy, new MessageFilter(limit: 1, offset: 1));

        // Act
        /** @var SendMessageStatus[] $result */
        $result = iterator_to_array($sut->__invoke($query));

        // Assert
        self::assertCount(1, $result);
        self::assertInstanceOf(SendMessageStatus::class, $result[0]);
        self::assertEquals($message2->messageId, $result[0]->messageId);
        self::assertEquals($message2->content, $result[0]->content);
        self::assertEquals('Unknown', $result[0]->status);

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
