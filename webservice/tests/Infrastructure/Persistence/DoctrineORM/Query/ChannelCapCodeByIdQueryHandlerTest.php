<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Persistence\DoctrineORM\Query;

use App\Core\IntelPage\Model\CapCode;
use App\Core\IntelPage\Model\Channel;
use App\Core\IntelPage\Query\ChannelCapCodeById;
use App\Infrastructure\Persistence\DoctrineORM\Query\ChannelCapCodeByIdQueryHandler;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Ulid;
use Zenstruck\Foundry\Test\ResetDatabase;

#[CoversClass(ChannelCapCodeByIdQueryHandler::class)]
#[CoversClass(ChannelCapCodeById::class)]
#[Group('integration'), Group('integration.database')]
final class ChannelCapCodeByIdQueryHandlerTest extends KernelTestCase
{
    use ResetDatabase;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = static::getContainer();
        $em = $container->get(EntityManagerInterface::class);
        $em->persist(new Channel(Ulid::fromString('01JT62N5PE9HBQTEZ1PPE6CJ4F'), 'Test Channel', CapCode::fromInt(222)));
        $em->flush();
        $em->clear();
    }

    public function testCanRetrieveCapCodeForChannel(): void
    {
        // Arrange
        $container = static::getContainer();
        $em = $container->get(EntityManagerInterface::class);

        $sut = new ChannelCapCodeByIdQueryHandler($em);

        $query = new ChannelCapCodeById('01JT62N5PE9HBQTEZ1PPE6CJ4F');

        // Act
        $result = $sut->__invoke($query);

        // Assert
        self::assertInstanceOf(CapCode::class, $result);
        self::assertSame(222, $result->getCode());
    }

    public function testReturnsNullIfChannelNotFound(): void
    {
        // Arrange
        $container = static::getContainer();
        $em = $container->get(EntityManagerInterface::class);

        $sut = new ChannelCapCodeByIdQueryHandler($em);

        $query = new ChannelCapCodeById('01JT61N5PE9HBQTEZ1PPE6CJ4F');

        // Act
        $result = $sut->__invoke($query);

        // Assert
        self::assertNull($result);
    }
}
