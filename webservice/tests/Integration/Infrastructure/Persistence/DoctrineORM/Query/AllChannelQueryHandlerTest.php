<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Persistence\DoctrineORM\Query;

use App\Core\IntelPage\Model\CapCode;
use App\Core\IntelPage\Model\Channel;
use App\Core\IntelPage\Query\AllChannel;
use App\Infrastructure\Persistence\DoctrineORM\Query\AllChannelQueryHandler;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Ulid;
use Zenstruck\Foundry\Test\ResetDatabase;

#[CoversClass(AllChannelQueryHandler::class)]
#[CoversClass(AllChannel::class)]
final class AllChannelQueryHandlerTest extends KernelTestCase
{
    use ResetDatabase;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = self::getContainer();
        $em = $container->get(EntityManagerInterface::class);
        $em->persist(new Channel(Ulid::fromString('01JT62N5PE9HBQTEZ1PPE6CJ4F'), 'Test Channel', CapCode::fromInt(222)));
        $em->persist(new Channel(Ulid::fromString('01JT62N5PE9HBQTEZ1PPE6CJ4C'), 'Test Channel', CapCode::fromInt(222)));
        $em->flush();
        $em->clear();
    }

    public function testCanRetrieveAllChannelWithoutFilter(): void
    {
        // Arrange
        $container = self::getContainer();
        $em = $container->get(EntityManagerInterface::class);

        $sut = new AllChannelQueryHandler($em);

        $query = AllChannel::withoutFilter();

        // Act
        $result = iterator_to_array($sut->__invoke($query));

        // Assert
        self::assertCount(2, $result);
        self::assertContainsOnlyInstancesOf(\App\Core\IntelPage\ReadModel\Channel::class, $result);
    }
}
