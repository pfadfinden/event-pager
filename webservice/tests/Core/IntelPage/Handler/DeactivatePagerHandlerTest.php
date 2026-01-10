<?php

declare(strict_types=1);

namespace App\Tests\Core\IntelPage\Handler;

use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\IntelPage\Command\DeactivatePager;
use App\Core\IntelPage\Exception\PagerNotFound;
use App\Core\IntelPage\Handler\DeactivatePagerHandler;
use App\Core\IntelPage\Model\Pager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[CoversClass(DeactivatePagerHandler::class), CoversClass(DeactivatePager::class)]
#[Group('unit')]
final class DeactivatePagerHandlerTest extends TestCase
{
    public function testCanDeactivatePager(): void
    {
        $id = '01JT62N5PE9HBQTEZ1PPE6CJ4F';
        $ulid = Ulid::fromString($id);
        $pager = new Pager($ulid, 'Test', 2);
        $pager->setActivated(true);

        $repositoryMock = self::createMock(EntityRepository::class);
        $repositoryMock->expects(self::once())->method('find')
            ->with($id)
            ->willReturn($pager);

        $entityManagerMock = self::createMock(EntityManagerInterface::class);
        $entityManagerMock->expects(self::once())->method('getRepository')
            ->with(Pager::class)
            ->willReturn($repositoryMock);
        $entityManagerMock->expects(self::once())->method('persist')
            ->with($pager);

        $unitOfWorkMock = self::createMock(UnitOfWork::class);
        $unitOfWorkMock->expects(self::once())->method('commit');

        // sut = Subject Under Test i.e. the class we are testing
        $sut = new DeactivatePagerHandler($entityManagerMock, $unitOfWorkMock);

        $cmd = new DeactivatePager($id);

        // ACT
        $sut->__invoke($cmd);

        // ASSERT
        self::assertFalse($pager->isActivated());
    }

    public function testThrowsExceptionWhenPagerNotFound(): void
    {
        $id = '01JT62N5PE9HBQTEZ1PPE6CJ4F';

        $repositoryMock = self::createMock(EntityRepository::class);
        $repositoryMock->expects(self::once())->method('find')
            ->with($id)
            ->willReturn(null);

        $entityManagerMock = self::createMock(EntityManagerInterface::class);
        $entityManagerMock->expects(self::once())->method('getRepository')
            ->with(Pager::class)
            ->willReturn($repositoryMock);

        $unitOfWorkMock = self::createMock(UnitOfWork::class);

        $sut = new DeactivatePagerHandler($entityManagerMock, $unitOfWorkMock);

        $cmd = new DeactivatePager($id);

        $this->expectException(PagerNotFound::class);
        $this->expectExceptionMessage('Pager with id "01JT62N5PE9HBQTEZ1PPE6CJ4F" was not found.');

        $sut->__invoke($cmd);
    }
}
