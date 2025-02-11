<?php

declare(strict_types=1);

namespace App\Core\UserManagement\Application;

use App\Core\Contracts\Bus\Bus;
use App\Core\UserManagement\Command\DeleteUser;
use App\Infrastructure\Repository\UserRepository;
use InvalidArgumentException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: Bus::COMMAND)]
final readonly class DeleteUserHandler
{
    public function __construct(
        private UserRepository $userRepository,
    ) {
    }

    public function __invoke(DeleteUser $command): void
    {
        $user = $this->userRepository->findOneBy(['username' => $command->getUsername()]);

        if (null === $user) {
            throw new InvalidArgumentException('User not found');
        }

        $this->userRepository->delete($user);
    }
}
