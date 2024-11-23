<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Persistence\DoctrineORM\Repository;

use App\Core\SendMessage\Model\IncomingMessage;
use App\Infrastructure\Persistence\DoctrineORM\Repository\DoctrineIncomingMessageRepository;
use Brick\DateTime\Clock\FixedClock;
use Brick\DateTime\DefaultClock;
use Brick\DateTime\Instant;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Ulid;
use Zenstruck\Foundry\Test\ResetDatabase;

#[Group('integration'), Group('integration.database')]
final class DoctrineIncomingMessageRepositoryTest extends KernelTestCase
{
    use ResetDatabase;

    public function testAddNewIncomingMessage(): void
    {
        // Arrange
        self::bootKernel();
        $container = static::getContainer();
        $em = $container->get(EntityManagerInterface::class);

        $sut = new DoctrineIncomingMessageRepository($em);

        $message = $this->testMessage();

        // Act
        $sut->add($message);
        $em->flush(); // in real code: use UOW to commit transaction

        // Assert
        $em->clear();
        /** @var IncomingMessage $result */
        $result = $em->find(IncomingMessage::class, $message->messageId);

        self::assertInstanceOf(IncomingMessage::class, $result);
        self::assertEquals('Hello World', $result->content);
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
