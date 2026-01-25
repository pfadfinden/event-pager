<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Persistence\DoctrineORM\Query;

use App\Core\IntelPage\Model\Pager;
use App\Infrastructure\Persistence\DoctrineORM\Query\PagerQueryHandler;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Ulid;
use Zenstruck\Foundry\Test\ResetDatabase;

#[CoversClass(PagerQueryHandler::class)]
#[CoversClass(\App\Core\IntelPage\Query\Pager::class)]
final class PagerQueryHandlerTest extends KernelTestCase
{
    use ResetDatabase;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = self::getContainer();
        $em = $container->get(EntityManagerInterface::class);
        $em->persist(new Pager(Ulid::fromString('01JT62N5PE9HBQTEZ1PPE6CJ4F'), 'Test Pager 1', 1));
        $em->persist(new Pager(Ulid::fromString('01JT62N5PE9HBQTEZ1PPE6CJ4C'), 'Test Pager 2', 2));
        $em->flush();
        $em->clear();
    }

    public function testCanRetrievePagerWithId(): void
    {
        // Arrange
        $container = self::getContainer();
        $em = $container->get(EntityManagerInterface::class);

        $sut = new PagerQueryHandler($em);

        $query = \App\Core\IntelPage\Query\Pager::withId('01JT62N5PE9HBQTEZ1PPE6CJ4C');

        // Act
        $result = $sut->__invoke($query);

        // Assert
        self::assertSame('Test Pager 2', $result->label);
    }
}
