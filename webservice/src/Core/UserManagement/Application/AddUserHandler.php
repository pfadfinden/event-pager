<?php

declare(strict_types=1);

namespace App\Core\UserManagement\Application;

use App\Core\Contracts\Bus\Bus;
use App\Core\UserManagement\Command\AddUser;
use App\Infrastructure\Entity\User;
use App\Infrastructure\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: Bus::COMMAND)]
final readonly class AddUserHandler
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    public function __invoke(AddUser $command): void
    {
        $user = new User();
        $user->setUsername($command->getUsername());
        $user->setDisplayname($command->getDisplayName());

        $passwordHasher = $this->userRepository->getPasswordHasher();
        $user->setPassword($passwordHasher->hashPassword($user, $command->getPassword()));

        $this->userRepository->save($user);
    }
}
