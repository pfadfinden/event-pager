<?php

declare(strict_types= 1);

namespace App\Tests\Core\UserManagement\Application;

use App\Core\UserManagement\Application\AddUserHandler;
use App\Core\UserManagement\Command\AddUser;
use App\Infrastructure\Repository\UserRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(AddUser::class)]
#[CoversClass(AddUserHandler::class)]
#[Group('unit')]
final class AddUserHandlerTest extends TestCase
{
    public function testAddUserCommand(): void
    {
        $repository = self::createMock(UserRepository::class);

        //TODO: Add expectations here    

        $sut = new AddUserHandler($repository);
        $command = AddUser::with(
            'test-user',
            'secure-password',
            'Test User'
        );
        $sut->__invoke($command);
    }
}