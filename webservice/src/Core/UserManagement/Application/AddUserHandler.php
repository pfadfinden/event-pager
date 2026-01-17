<?php

declare(strict_types=1);

namespace App\Core\UserManagement\Application;

use App\Core\Contracts\Bus\Bus;
use App\Core\UserManagement\Command\AddUser;
use App\Core\UserManagement\Model\User;
use App\Infrastructure\Persistence\DoctrineORM\Repository\UserRepository;
use InvalidArgumentException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsMessageHandler(bus: Bus::COMMAND)]
final readonly class AddUserHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function __invoke(AddUser $command): void
    {
        if (null !== $this->userRepository->findOneByUsername($command->getUsername())) {
            throw new InvalidArgumentException('User already exists');
        }

        $user = new User($command->getUsername());
        $user->setDisplayname($command->getDisplayName());

        $user->setPassword($this->passwordHasher->hashPassword($user, $command->getPassword()));

        $this->userRepository->save($user);
    }
}
