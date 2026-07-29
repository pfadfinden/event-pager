<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\UserManagement\Application;

use App\Core\MessageRecipient\Model\Person;
use App\Core\UserManagement\Application\LinkRecipientToUserHandler;
use App\Core\UserManagement\Command\LinkRecipientToUser;
use App\Core\UserManagement\Model\User;
use App\Infrastructure\Persistence\DoctrineORM\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[CoversClass(LinkRecipientToUser::class)]
#[CoversClass(LinkRecipientToUserHandler::class)]
#[Group('unit')]
final class LinkRecipientToUserHandlerTest extends TestCase
{
    public function testCanLinkUserToRecipient(): void
    {
        $user = new User('test-user');
        $recipient = new Person('John Doe');

        $repository = self::createMock(UserRepository::class);
        $repository->expects(self::once())
            ->method('findOneByUsername')
            ->with('test-user')
            ->willReturn($user);
        $repository->expects(self::once())
            ->method('save')
            ->with(self::callback(fn (User $savedUser): bool => $recipient === $savedUser->getRecipient()));

        $em = self::createMock(EntityManagerInterface::class);
        $em->expects(self::once())
            ->method('find')
            ->with(self::anything(), self::equalTo($recipient->getId()))
            ->willReturn($recipient);

        $handler = new LinkRecipientToUserHandler($repository, $em);
        $command = LinkRecipientToUser::with('test-user', $recipient->getId()->toString());

        $handler->__invoke($command);
    }

    public function testCanUnlinkUserFromRecipient(): void
    {
        $user = new User('test-user');
        $user->setRecipient(new Person('John Doe'));

        $repository = self::createMock(UserRepository::class);
        $repository->expects(self::once())
            ->method('findOneByUsername')
            ->with('test-user')
            ->willReturn($user);
        $repository->expects(self::once())
            ->method('save')
            ->with(self::callback(fn (User $savedUser): bool => null === $savedUser->getRecipient()));

        $em = self::createMock(EntityManagerInterface::class);
        $em->expects(self::never())->method('find');

        $handler = new LinkRecipientToUserHandler($repository, $em);
        $command = LinkRecipientToUser::with('test-user', null);

        $handler->__invoke($command);
    }

    public function testThrowsExceptionWhenUserNotFound(): void
    {
        $repository = self::createMock(UserRepository::class);
        $repository->expects(self::once())
            ->method('findOneByUsername')
            ->with('nonexistent-user')
            ->willReturn(null);
        $repository->expects(self::never())->method('save');

        $em = self::createMock(EntityManagerInterface::class);

        $handler = new LinkRecipientToUserHandler($repository, $em);
        $command = LinkRecipientToUser::with('nonexistent-user', (string) new Ulid());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('User not found');

        $handler->__invoke($command);
    }

    public function testThrowsExceptionWhenRecipientNotFound(): void
    {
        $user = new User('test-user');

        $repository = self::createMock(UserRepository::class);
        $repository->expects(self::once())
            ->method('findOneByUsername')
            ->with('test-user')
            ->willReturn($user);
        $repository->expects(self::never())->method('save');

        $em = self::createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('find')->willReturn(null);

        $handler = new LinkRecipientToUserHandler($repository, $em);
        $command = LinkRecipientToUser::with('test-user', (string) new Ulid());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Recipient not found');

        $handler->__invoke($command);
    }
}
