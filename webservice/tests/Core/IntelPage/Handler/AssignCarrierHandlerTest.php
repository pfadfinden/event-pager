<?php

declare(strict_types=1);

namespace App\Tests\Core\IntelPage\Handler;

use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\IntelPage\Command\AssignCarrier;
use App\Core\IntelPage\Exception\PagerNotFound;
use App\Core\IntelPage\Handler\AssignCarrierHandler;
use App\Core\IntelPage\Model\Pager;
use App\Core\MessageRecipient\Model\Person;
use App\Core\MessageRecipient\Port\MessageRecipientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[CoversClass(AssignCarrierHandler::class), CoversClass(AssignCarrier::class)]
#[Group('unit')]
final class AssignCarrierHandlerTest extends TestCase
{
    public function testCanAssignCarrier(): void
    {
        $pagerId = '01JT62N5PE9HBQTEZ1PPE6CJ4F';
        $pagerUlid = Ulid::fromString($pagerId);
        $pager = new Pager($pagerUlid, 'Test', 2);

        $recipientId = '01JT62N5PE9HBQTEZ1PPE6CJ4G';
        $recipientUlid = Ulid::fromString($recipientId);
        $recipient = new Person('John Doe', $recipientUlid);

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

        $recipientRepositoryMock = self::createMock(MessageRecipientRepository::class);
        $recipientRepositoryMock->expects(self::once())->method('getRecipientFromID')
            ->with($recipientUlid)
            ->willReturn($recipient);

        $unitOfWorkMock = self::createMock(UnitOfWork::class);
        $unitOfWorkMock->expects(self::once())->method('commit');

        $sut = new AssignCarrierHandler($entityManagerMock, $recipientRepositoryMock, $unitOfWorkMock);

        $cmd = new AssignCarrier($pagerId, $recipientId);

        $sut->__invoke($cmd);

        self::assertSame($recipient, $pager->getCarriedBy());
    }

    public function testCanClearCarrier(): void
    {
        $pagerId = '01JT62N5PE9HBQTEZ1PPE6CJ4F';
        $pagerUlid = Ulid::fromString($pagerId);
        $pager = new Pager($pagerUlid, 'Test', 2);

        $recipientUlid = Ulid::fromString('01JT62N5PE9HBQTEZ1PPE6CJ4G');
        $existingRecipient = new Person('John Doe', $recipientUlid);
        $pager->setCarriedBy($existingRecipient);

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

        $recipientRepositoryMock = self::createMock(MessageRecipientRepository::class);
        $recipientRepositoryMock->expects(self::never())->method('getRecipientFromID');

        $unitOfWorkMock = self::createMock(UnitOfWork::class);
        $unitOfWorkMock->expects(self::once())->method('commit');

        $sut = new AssignCarrierHandler($entityManagerMock, $recipientRepositoryMock, $unitOfWorkMock);

        $cmd = new AssignCarrier($pagerId, null);

        $sut->__invoke($cmd);

        self::assertNull($pager->getCarriedBy());
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

        $recipientRepositoryMock = self::createStub(MessageRecipientRepository::class);
        $unitOfWorkMock = self::createStub(UnitOfWork::class);

        $sut = new AssignCarrierHandler($entityManagerMock, $recipientRepositoryMock, $unitOfWorkMock);

        $cmd = new AssignCarrier($pagerId, '01JT62N5PE9HBQTEZ1PPE6CJ4G');

        $this->expectException(PagerNotFound::class);
        $this->expectExceptionMessage('Pager with id "01JT62N5PE9HBQTEZ1PPE6CJ4F" was not found.');

        $sut->__invoke($cmd);
    }
}
