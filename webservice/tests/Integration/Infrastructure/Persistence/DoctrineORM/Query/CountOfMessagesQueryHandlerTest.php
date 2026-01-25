<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Persistence\DoctrineORM\Query;

use App\Core\SendMessage\Model\IncomingMessage;
use App\Core\SendMessage\Query\CountOfSendMessages;
use App\Infrastructure\Persistence\DoctrineORM\Query\CountOfSentMessagesQueryHandler;
use Brick\DateTime\Clock\FixedClock;
use Brick\DateTime\DefaultClock;
use Brick\DateTime\Duration;
use Brick\DateTime\Instant;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Ulid;
use Zenstruck\Foundry\Test\ResetDatabase;

#[CoversClass(CountOfSendMessages::class)]
#[CoversClass(CountOfSentMessagesQueryHandler::class)]
final class CountOfMessagesQueryHandlerTest extends KernelTestCase
{
    use ResetDatabase;

    public function testCanCountAllMessages(): void
    {
        // Arrange
        $message1 = $this->testMessage();
        $message2 = $this->testMessage();

        self::bootKernel();
        $container = self::getContainer();
        $em = $container->get(EntityManagerInterface::class);

        // Create fixtures
        $em->persist($message1);
        $em->persist($message2);
        $em->flush();

        $sut = new CountOfSentMessagesQueryHandler($em);

        $query = CountOfSendMessages::all();

        // Act
        $result = $sut->__invoke($query);

        // Assert
        self::assertSame(2, $result);
    }

    public function testCanCountAllMessagesSince(): void
    {
        // Arrange
        $message1 = $this->testMessage(preDate: Duration::ofMinutes(60 * 24 + 1));
        $message2 = $this->testMessage(preDate: Duration::ofMinutes(60 * 24 - 1));
        $message3 = $this->testMessage();

        self::bootKernel();
        $container = self::getContainer();
        $em = $container->get(EntityManagerInterface::class);

        // Create fixtures
        $em->persist($message1);
        $em->persist($message2);
        $em->persist($message3);
        $em->flush();

        $sut = new CountOfSentMessagesQueryHandler($em);

        $query = CountOfSendMessages::allSendSince(Instant::of(1_000_000_000)->minus(Duration::ofHours(24)));

        // Act
        $result = $sut->__invoke($query);

        // Assert
        self::assertSame(2, $result);
    }

    public function testCanCountMessagesSendByOneUser(): void
    {
        // Arrange
        $message1 = $this->testMessage();
        $message2 = $this->testMessage();

        self::bootKernel();
        $container = self::getContainer();
        $em = $container->get(EntityManagerInterface::class);

        // Create fixtures
        $em->persist($message1);
        $em->persist($message2);
        $em->flush();

        $sut = new CountOfSentMessagesQueryHandler($em);

        $query = CountOfSendMessages::sendByUser($message1->by->toString());

        // Act
        $result = $sut->__invoke($query);

        // Assert
        self::assertSame(1, $result);
    }

    private function testMessage(?string $sendBy = null, ?Duration $preDate = null): IncomingMessage
    {
        DefaultClock::set(new FixedClock(Instant::of(1_000_000_000)->minus($preDate ?? Duration::zero())));
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
