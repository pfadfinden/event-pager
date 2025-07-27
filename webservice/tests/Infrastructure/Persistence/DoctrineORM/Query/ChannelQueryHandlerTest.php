<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Persistence\DoctrineORM\Query;

use App\Core\IntelPage\Model\CapCode;
use App\Core\IntelPage\Model\Channel;
use App\Infrastructure\Persistence\DoctrineORM\Query\ChannelQueryHandler;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Ulid;
use Zenstruck\Foundry\Test\ResetDatabase;

#[CoversClass(ChannelQueryHandler::class)]
#[CoversClass(\App\Core\IntelPage\Query\Channel::class)]
#[Group('integration'), Group('integration.database')]
final class ChannelQueryHandlerTest extends KernelTestCase
{
    use ResetDatabase;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = static::getContainer();
        $em = $container->get(EntityManagerInterface::class);
        $em->persist(new Channel(Ulid::fromString('01JT62N5PE9HBQTEZ1PPE6CJ4F'), 'Test Channel 1', CapCode::fromInt(222)));
        $em->persist(new Channel(Ulid::fromString('01JT62N5PE9HBQTEZ1PPE6CJ4C'), 'Test Channel 2', CapCode::fromInt(222)));
        $em->flush();
        $em->clear();
    }

    public function testCanRetrieveChannelWithId(): void
    {
        // Arrange
        $container = static::getContainer();
        $em = $container->get(EntityManagerInterface::class);

        $sut = new ChannelQueryHandler($em);

        $query = \App\Core\IntelPage\Query\Channel::withId('01JT62N5PE9HBQTEZ1PPE6CJ4C');

        // Act
        $result = $sut->__invoke($query);

        // Assert
        self::assertInstanceOf(\App\Core\IntelPage\ReadModel\Channel::class, $result);
        self::assertSame('Test Channel 2', $result->name);
    }
}
