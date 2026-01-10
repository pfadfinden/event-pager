<?php

declare(strict_types=1);

namespace App\Tests\Core\IntelPage\Handler;

use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\IntelPage\Command\RemovePager;
use App\Core\IntelPage\Handler\RemovePagerHandler;
use App\Core\IntelPage\Model\Pager;
use App\Core\IntelPage\Port\PagerRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[CoversClass(RemovePagerHandler::class), CoversClass(RemovePager::class)]
#[Group('unit')]
final class RemovePagerHandlerTest extends TestCase
{
    public function testCanRemovePager(): void
    {
        $id = '01JT62N5PE9HBQTEZ1PPE6CJ4F';
        $ulid = Ulid::fromString($id);
        $pager = new Pager($ulid, 'Test', 2);

        $pagerRepositoryMock = self::createMock(PagerRepository::class);

        $pagerRepositoryMock->expects(self::once())->method('getById')
            ->with(self::callback(fn (Ulid $ulidX) => $ulidX->equals($ulid)))
            ->willReturn($pager);
        $pagerRepositoryMock->expects(self::once())->method('remove')
            ->with($pager);

        $unitOfWorkMock = self::createMock(UnitOfWork::class);
        $unitOfWorkMock->expects(self::once())->method('commit');

        // sut = Subject Under Test i.e. the class we are testing
        $sut = new RemovePagerHandler($pagerRepositoryMock, $unitOfWorkMock);

        $cmd = new RemovePager($id);

        // ACT
        $sut->__invoke($cmd);
    }

    public function testDoesNotThrowWhenPagerNotFound(): void
    {
        $id = '01JT62N5PE9HBQTEZ1PPE6CJ4F';
        $ulid = Ulid::fromString($id);

        $pagerRepositoryMock = self::createMock(PagerRepository::class);
        $pagerRepositoryMock->expects(self::once())->method('getById')
            ->with(self::callback(fn (Ulid $ulidX) => $ulidX->equals($ulid)))
            ->willReturn(null);
        $pagerRepositoryMock->expects(self::never())->method('remove');

        $unitOfWorkMock = self::createMock(UnitOfWork::class);
        $unitOfWorkMock->expects(self::never())->method('commit');

        // sut = Subject Under Test i.e. the class we are testing
        $sut = new RemovePagerHandler($pagerRepositoryMock, $unitOfWorkMock);

        $cmd = new RemovePager($id);

        // ACT - should not throw any exception
        $sut->__invoke($cmd);

        // ASSERT - If we reach here, the test passes (no exception was thrown)
        // The mock expectations above verify that remove and commit are never called
    }
}
