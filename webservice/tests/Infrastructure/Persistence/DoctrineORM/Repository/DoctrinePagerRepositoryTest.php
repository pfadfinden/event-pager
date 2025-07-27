<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Persistence\DoctrineORM\Repository;

use App\Core\IntelPage\Model\Pager;
use App\Infrastructure\Persistence\DoctrineORM\Repository\DoctrinePagerRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Ulid;
use Zenstruck\Foundry\Test\ResetDatabase;
use function assert;

#[CoversClass(DoctrinePagerRepository::class)]
#[Group('integration'), Group('integration.database')]
final class DoctrinePagerRepositoryTest extends KernelTestCase
{
    use ResetDatabase;

    public function testPersistNewPager(): void
    {
        // Arrange
        self::bootKernel();
        $container = static::getContainer();
        $em = $container->get(EntityManagerInterface::class);

        $sut = new DoctrinePagerRepository($em);

        $pager = $this->newPager();

        // Act
        $sut->persist($pager);

        $em->flush();
        $em->clear();

        // Assert
        /** @var Pager $result */
        $result = $em->find(Pager::class, Ulid::fromString('02JT62N5PE9HBQTEZ1PPE6CJ4C'));

        self::assertEquals('Sample', $result->getLabel());

        // Cleanup
        $em->remove($result);
        $em->flush();
    }

    public function testGetById(): void
    {
        // Arrange
        self::bootKernel();
        $container = static::getContainer();

        $pager = $this->newPager();

        $em = $container->get(EntityManagerInterface::class);
        $em->persist($pager);
        $em->flush();
        $em->clear();

        $sut = new DoctrinePagerRepository($em);

        // Act
        $result = $sut->getById(Ulid::fromString('02JT62N5PE9HBQTEZ1PPE6CJ4C'));

        // Assert
        self::assertInstanceOf(Pager::class, $result);
        self::assertEquals('Sample', $result->getLabel());

        // Cleanup
        $em->remove($result);
        $em->flush();
    }

    public function testRemoveById(): void
    {
        // Arrange
        self::bootKernel();
        $container = static::getContainer();

        $pager = $this->newPager();

        $em = $container->get(EntityManagerInterface::class);
        $em->persist($pager);
        $em->flush();
        $em->clear();

        $sut = new DoctrinePagerRepository($em);
        $pager1 = $sut->getById(Ulid::fromString('02JT62N5PE9HBQTEZ1PPE6CJ4C'));
        assert(null !== $pager1);

        // Act
        $sut->remove($pager1);
        $em->flush();

        // Assert
        self::assertNull($sut->getById(Ulid::fromString('02JT62N5PE9HBQTEZ1PPE6CJ4C')));
    }

    private function newPager(): Pager
    {
        return new Pager(
            Ulid::fromString('02JT62N5PE9HBQTEZ1PPE6CJ4C'),
            'Sample',
            2,
        );
    }
}
