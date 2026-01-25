<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\IntelPage\Handler;

use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\IntelPage\Command\AssignIndividualCapCode;
use App\Core\IntelPage\Exception\PagerNotFound;
use App\Core\IntelPage\Handler\AssignIndividualCapCodeHandler;
use App\Core\IntelPage\Model\Pager;
use App\Core\IntelPage\Model\Slot;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[CoversClass(AssignIndividualCapCodeHandler::class), CoversClass(AssignIndividualCapCode::class)]
final class AssignIndividualCapCodeHandlerTest extends TestCase
{
    public function testCanAssignIndividualCapCode(): void
    {
        $pagerId = '01JT62N5PE9HBQTEZ1PPE6CJ4F';
        $pagerUlid = Ulid::fromString($pagerId);
        $slot = 1;
        $capCode = 1234;

        $pager = new Pager($pagerUlid, 'Test', 2);

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
        $sut = new AssignIndividualCapCodeHandler($entityManagerMock, $unitOfWorkMock);

        $cmd = new AssignIndividualCapCode($pagerId, $slot, $capCode, true, false);

        // ACT
        $sut->__invoke($cmd);

        // ASSERT
        self::assertNotNull($pager->getCapAssignment(Slot::fromInt($slot)));
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

        $sut = new AssignIndividualCapCodeHandler($entityManagerMock, $unitOfWorkMock);

        $cmd = new AssignIndividualCapCode($pagerId, 1, 1234, true, false);

        $this->expectException(PagerNotFound::class);
        $this->expectExceptionMessage('Pager with id "01JT62N5PE9HBQTEZ1PPE6CJ4F" was not found.');

        $sut->__invoke($cmd);
    }
}
