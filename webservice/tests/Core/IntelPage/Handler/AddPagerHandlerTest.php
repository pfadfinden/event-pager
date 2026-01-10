<?php

declare(strict_types=1);

namespace App\Tests\Core\IntelPage\Handler;

use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\IntelPage\Command\AddPager;
use App\Core\IntelPage\Handler\AddPagerHandler;
use App\Core\IntelPage\Model\Pager;
use App\Core\IntelPage\Port\PagerRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[CoversClass(AddPagerHandler::class), CoversClass(AddPager::class)]
#[Group('unit')]
final class AddPagerHandlerTest extends TestCase
{
    public function testCanAddPager(): void
    {
        $id = Ulid::generate();

        $pagerRepositoryMock = self::createMock(PagerRepository::class);
        $pagerRepositoryMock->expects(self::once())->method('persist')
            ->with(self::callback(fn (Pager $pager) => (
                'default' === $pager->getLabel()
                && $pager->getId()->equals(Ulid::fromString($id))
                && 3 === $pager->getNumber()
            )));
        // Pot. Improvement: Assert all properties of pager are correct

        $unitOfWorkMock = self::createMock(UnitOfWork::class);
        $unitOfWorkMock->expects(self::once())->method('commit');

        // sut = Subject Under Test i.e. the class we are testing
        $sut = new AddPagerHandler($pagerRepositoryMock, $unitOfWorkMock);

        $cmd = new AddPager(
            $id,
            'default',
            3,
        );

        // ACT
        $sut->__invoke($cmd);
    }
}
