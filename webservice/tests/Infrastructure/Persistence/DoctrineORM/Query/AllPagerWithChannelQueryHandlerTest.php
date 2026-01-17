<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Persistence\DoctrineORM\Query;

use App\Core\IntelPage\Model\CapCode;
use App\Core\IntelPage\Model\Channel;
use App\Core\IntelPage\Model\Pager;
use App\Core\IntelPage\Model\Slot;
use App\Core\IntelPage\Query\AllPagerWithChannel;
use App\Core\IntelPage\ReadModel\PagerInChannel;
use App\Infrastructure\Persistence\DoctrineORM\Query\AllPagerWithChannelQueryHandler;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Ulid;
use Zenstruck\Foundry\Test\ResetDatabase;

#[CoversClass(AllPagerWithChannelQueryHandler::class)]
#[CoversClass(AllPagerWithChannel::class)]
#[Group('integration'), Group('integration.database')]
final class AllPagerWithChannelQueryHandlerTest extends KernelTestCase
{
    use ResetDatabase;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = self::getContainer();
        $em = $container->get(EntityManagerInterface::class);

        $ch1 = new Channel(Ulid::fromString('02JT62N5PE9HBQTEZ1PPE6CJ4D'), 'Test Channel 1', CapCode::fromInt(222));
        $ch2 = new Channel(Ulid::fromString('02JT62N5PE9HBQTEZ1PPE6CJ4E'), 'Test Channel 2', CapCode::fromInt(223));
        $em->persist($ch1);
        $em->persist($ch2);

        $pager1 = new Pager(Ulid::fromString('02JT62N5PE9HBQTEZ1PPE6CJ4F'), 'Test Pager 1', 1);
        $pager1->assignIndividualCap(Slot::fromInt(0), CapCode::fromInt(2223), false, false);
        $pager1->assignChannel(Slot::fromInt(1), $ch1);
        $pager1->assignIndividualCap(Slot::fromInt(2), CapCode::fromInt(2225), false, true);
        $pager1->assignIndividualCap(Slot::fromInt(3), CapCode::fromInt(2226), true, true);
        $em->persist($pager1);

        $pager2 = new Pager(Ulid::fromString('02JT62N5PE9HBQTEZ1PPE6CJ4C'), 'Test Pager 2', 2);
        $pager2->assignIndividualCap(Slot::fromInt(0), CapCode::fromInt(3223), false, false);
        $pager1->assignChannel(Slot::fromInt(1), $ch2);
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

        $sut = new AllPagerWithChannelQueryHandler($em);

        $query = AllPagerWithChannel::withId('02JT62N5PE9HBQTEZ1PPE6CJ4D');

        // Act
        $result = iterator_to_array($sut->__invoke($query));

        // Assert
        self::assertCount(1, $result);
        self::assertContainsOnlyInstancesOf(PagerInChannel::class, $result);
    }
}
