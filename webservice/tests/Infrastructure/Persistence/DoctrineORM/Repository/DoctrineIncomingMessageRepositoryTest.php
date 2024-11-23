<?php

declare(strict_types=1);

namespace App\Tests\Core\SendMessage\Model;

use App\Core\SendMessage\Model\IncomingMessage;
use App\Infrastructure\Persistence\DoctrineORM\Repository\DoctrineIncomingMessageRepository as RepositoryDoctrineIncomingMessageRepository;
use Brick\DateTime\Clock\FixedClock;
use Brick\DateTime\DefaultClock;
use Brick\DateTime\Instant;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Ulid;

#[Group('integration'), Group('integration.database')]
final class DoctrineIncomingMessageRepositoryTest extends KernelTestCase
{
    public function testAddNewIncomingMessage(): void
    {
        // Arrange
        self::bootKernel();
        $container = static::getContainer();
        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        $sut = new RepositoryDoctrineIncomingMessageRepository($em);

        $message = $this->testMessage();

        // Act
        $sut->add($message);

        // Assert
        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);
        /** @var IncomingMessage $result */
        $result = $em->find(IncomingMessage::class, $message->messageId);

        self::assertInstanceOf(IncomingMessage::class, $result);
        self::assertEquals('Hello World', $result->content);

        // Cleanup
        $em->remove($message);
        $em->flush();
    }

    private function testMessage(): IncomingMessage
    {
        DefaultClock::set(new FixedClock(Instant::of(1_000_000_000)));
        $message = IncomingMessage::new(
            Ulid::fromString(Ulid::generate()),
            [Ulid::fromString(Ulid::generate()), Ulid::fromString(Ulid::generate())],
            'Hello World',
            1
        );
        DefaultClock::reset();

        return $message;
    }
}
