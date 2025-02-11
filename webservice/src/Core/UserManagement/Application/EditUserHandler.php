<?php

declare(strict_types=1);

namespace App\Core\UserManagement\Application;

use App\Core\Contracts\Bus\Bus;
use App\Core\UserManagement\Command\EditUser;
use App\Infrastructure\Repository\UserRepository;
use InvalidArgumentException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsMessageHandler(bus: Bus::COMMAND)]
final readonly class EditUserHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function __invoke(EditUser $command): void
    {
        $user = $this->userRepository->findOneBy(['username' => $command->getUsername()]);

        if (null === $user) {
            throw new InvalidArgumentException('User not found');
        }

        $plaintextPassword = $command->getPassword();
        if (null !== $plaintextPassword) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $plaintextPassword));
        }

        $displayname = $command->getDisplayname();
        if (null !== $displayname) {
            $user->setDisplayname($displayname);
        }

        $removeRoles = $command->getRevokeRoles();
        if (null !== $removeRoles) {
            foreach ($removeRoles as $role) {
                $user->removeRole($role);
            }
        }

        $addRoles = $command->getAddRoles();
        if (null !== $addRoles) {
            $user->addRoles($addRoles);
        }

        $this->userRepository->save($user);
    }
}
