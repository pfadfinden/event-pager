<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\IntelPage\Handler;

use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\IntelPage\Command\ClearSlot;
use App\Core\IntelPage\Exception\PagerNotFound;
use App\Core\IntelPage\Handler\ClearSlotHandler;
use App\Core\IntelPage\Model\CapCode;
use App\Core\IntelPage\Model\Pager;
use App\Core\IntelPage\Model\Slot;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[CoversClass(ClearSlotHandler::class), CoversClass(ClearSlot::class)]
final class ClearSlotHandlerTest extends TestCase
{
    public function testCanClearSlot(): void
    {
        $pagerId = '01JT62N5PE9HBQTEZ1PPE6CJ4F';
        $pagerUlid = Ulid::fromString($pagerId);
        $slot = 1;

        $pager = new Pager($pagerUlid, 'Test', 2);
        $pager->assignIndividualCap(Slot::fromInt($slot), CapCode::fromInt(1234), true, false);

        $repositoryMock = self::createMock(EntityRepository::class);
        $repositoryMock->expects(self::once())->method('find')
            ->with($pagerId)
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
        $sut = new ClearSlotHandler($entityManagerMock, $unitOfWorkMock);

        $cmd = new ClearSlot($pagerId, $slot);

        // ACT
        $sut->__invoke($cmd);

        // ASSERT
        self::assertNull($pager->getCapAssignment(Slot::fromInt($slot)));
    }

    public function testDoesNothingWhenSlotAlreadyEmpty(): void
    {
        $pagerId = '01JT62N5PE9HBQTEZ1PPE6CJ4F';
        $pagerUlid = Ulid::fromString($pagerId);
        $slot = 1;

        $pager = new Pager($pagerUlid, 'Test', 2);

        $repositoryMock = self::createMock(EntityRepository::class);
        $repositoryMock->expects(self::once())->method('find')
            ->with($pagerId)
            ->willReturn($pager);

        $entityManagerMock = self::createMock(EntityManagerInterface::class);
        $entityManagerMock->expects(self::once())->method('getRepository')
            ->with(Pager::class)
            ->willReturn($repositoryMock);
        $entityManagerMock->expects(self::never())->method('persist');

        $unitOfWorkMock = self::createMock(UnitOfWork::class);
        $unitOfWorkMock->expects(self::never())->method('commit');

        // sut = Subject Under Test i.e. the class we are testing
        $sut = new ClearSlotHandler($entityManagerMock, $unitOfWorkMock);

        $cmd = new ClearSlot($pagerId, $slot);

        // ACT
        $sut->__invoke($cmd);

        // ASSERT
        self::assertNull($pager->getCapAssignment(Slot::fromInt($slot)));
    }

    public function testThrowsExceptionWhenPagerNotFound(): void
    {
        $pagerId = '01JT62N5PE9HBQTEZ1PPE6CJ4F';

        $repositoryMock = self::createMock(EntityRepository::class);
        $repositoryMock->expects(self::once())->method('find')
            ->with($pagerId)
            ->willReturn(null);

        $entityManagerMock = self::createMock(EntityManagerInterface::class);
        $entityManagerMock->expects(self::once())->method('getRepository')
            ->with(Pager::class)
            ->willReturn($repositoryMock);

        $unitOfWorkMock = self::createStub(UnitOfWork::class);

        $sut = new ClearSlotHandler($entityManagerMock, $unitOfWorkMock);

        $cmd = new ClearSlot($pagerId, 1);

        $this->expectException(PagerNotFound::class);
        $this->expectExceptionMessage('Pager with id "01JT62N5PE9HBQTEZ1PPE6CJ4F" was not found.');

        $sut->__invoke($cmd);
    }
}
