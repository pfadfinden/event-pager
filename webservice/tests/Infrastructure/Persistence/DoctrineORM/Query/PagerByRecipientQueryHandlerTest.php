<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Persistence\DoctrineORM\Query;

use App\Core\IntelPage\Model\Pager;
use App\Core\IntelPage\Query\PagerByRecipient;
use App\Core\MessageRecipient\Model\Person;
use App\Infrastructure\Persistence\DoctrineORM\Query\PagerByRecipientQueryHandler;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Ulid;
use Zenstruck\Foundry\Test\ResetDatabase;

#[CoversClass(PagerByRecipientQueryHandler::class)]
#[Group('integration'), Group('integration.database')]
final class PagerByRecipientQueryHandlerTest extends KernelTestCase
{
    use ResetDatabase;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = static::getContainer();
        $em = $container->get(EntityManagerInterface::class);
        $carriedBy = new Person('test', Ulid::fromString('01JT62N5PE9HBQTEZ1PPE6CJ4F'));
        $em->persist($carriedBy);
        $pager = new Pager(Ulid::fromString('02JT62N5PE9HBQTEZ1PPE6CJ4F'), 'Test Channel', 1);
        $pager->setCarriedBy($carriedBy);
        $em->persist($pager);
        $em->flush();
        $em->clear();
    }

    public function testCanRetrievePagerForRecipient(): void
    {
        // Arrange
        $container = static::getContainer();
        $em = $container->get(EntityManagerInterface::class);

        $sut = new PagerByRecipientQueryHandler($em);

        $query = PagerByRecipient::withId('01JT62N5PE9HBQTEZ1PPE6CJ4F');

        // Act
        $result = $sut->__invoke($query);

        // Assert
        self::assertInstanceOf(Pager::class, $result);
        self::assertSame('02JT62N5PE9HBQTEZ1PPE6CJ4F', $result->getId()->toString());
    }

    public function testReturnsNullIfPagerNotFound(): void
    {
        // Arrange
        $container = static::getContainer();
        $em = $container->get(EntityManagerInterface::class);

        $sut = new PagerByRecipientQueryHandler($em);

        $query = PagerByRecipient::withId('03JT62N5PE9HBQTEZ1PPE6CJ4D');

        // Act
        $result = $sut->__invoke($query);

        // Assert
        self::assertNull($result);
    }
}
