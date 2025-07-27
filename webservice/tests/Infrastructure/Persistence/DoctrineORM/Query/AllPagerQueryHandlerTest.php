<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Persistence\DoctrineORM\Query;

use App\Core\IntelPage\Model\Pager;
use App\Core\IntelPage\Query\AllPager;
use App\Infrastructure\Persistence\DoctrineORM\Query\AllPagerQueryHandler;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Ulid;
use Zenstruck\Foundry\Test\ResetDatabase;

#[CoversClass(AllPagerQueryHandler::class)]
#[CoversClass(AllPager::class)]
#[Group('integration'), Group('integration.database')]
final class AllPagerQueryHandlerTest extends KernelTestCase
{
    use ResetDatabase;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = static::getContainer();
        $em = $container->get(EntityManagerInterface::class);
        $em->persist(new Pager(Ulid::fromString('02JT62N5PE9HBQTEZ1PPE6CJ4F'), 'Test Pager 1', 1));
        $em->persist(new Pager(Ulid::fromString('02JT62N5PE9HBQTEZ1PPE6CJ4C'), 'Test Pager 2', 2));
        $em->flush();
        $em->clear();
    }

    public function testCanRetrieveAllPagerWithoutFilter(): void
    {
        // Arrange
        $container = static::getContainer();
        $em = $container->get(EntityManagerInterface::class);

        $sut = new AllPagerQueryHandler($em);

        $query = AllPager::withoutFilter();

        // Act
        $result = iterator_to_array($sut->__invoke($query));

        // Assert
        self::assertCount(2, $result);
        self::assertContainsOnlyInstancesOf(\App\Core\IntelPage\ReadModel\Pager::class, $result);
    }
}
