<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Persistence\DoctrineORM\Query;

use App\Core\IntelPage\Model\CapCode;
use App\Core\IntelPage\Model\Pager;
use App\Core\IntelPage\Model\Slot;
use App\Core\IntelPage\Query\CapAssignments;
use App\Core\IntelPage\ReadModel\CapAssignment;
use App\Infrastructure\Persistence\DoctrineORM\Query\CapAssignmentsQueryHandler;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Ulid;
use Zenstruck\Foundry\Test\ResetDatabase;

#[CoversClass(CapAssignmentsQueryHandler::class)]
#[CoversClass(CapAssignments::class)]
final class CapAssignmentsQueryHandlerTest extends KernelTestCase
{
    use ResetDatabase;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = self::getContainer();
        $em = $container->get(EntityManagerInterface::class);
        $pager1 = new Pager(Ulid::fromString('02JT62N5PE9HBQTEZ1PPE6CJ4F'), 'Test Pager 1', 1);
        $pager1->assignIndividualCap(Slot::fromInt(0), CapCode::fromInt(2223), false, false);
        $pager1->assignIndividualCap(Slot::fromInt(1), CapCode::fromInt(2224), true, false);
        $pager1->assignIndividualCap(Slot::fromInt(2), CapCode::fromInt(2225), false, true);
        $pager1->assignIndividualCap(Slot::fromInt(3), CapCode::fromInt(2226), true, true);
        $em->persist($pager1);
        $pager2 = new Pager(Ulid::fromString('02JT62N5PE9HBQTEZ1PPE6CJ4C'), 'Test Pager 2', 2);
        $pager2->assignIndividualCap(Slot::fromInt(0), CapCode::fromInt(3223), false, false);
        $pager2->assignIndividualCap(Slot::fromInt(1), CapCode::fromInt(3224), true, false);
        $pager2->assignIndividualCap(Slot::fromInt(2), CapCode::fromInt(3225), false, true);
        $pager2->assignIndividualCap(Slot::fromInt(3), CapCode::fromInt(3226), true, true);
        $em->persist($pager2);
        $em->flush();
        $em->clear();
    }

    public function testCanRetrieveAllCapAssignmentsByPager(): void
    {
        // Arrange
        $container = self::getContainer();
        $em = $container->get(EntityManagerInterface::class);

        $sut = new CapAssignmentsQueryHandler($em);

        $query = CapAssignments::forPagerWithId('02JT62N5PE9HBQTEZ1PPE6CJ4F');

        // Act
        $result = iterator_to_array($sut->__invoke($query));

        // Assert
        self::assertCount(4, $result);
        self::assertContainsOnlyInstancesOf(CapAssignment::class, $result);
    }
}
