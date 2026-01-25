<?php

declare(strict_types=1);

namespace App\Tests\Factory;

use App\Core\UserManagement\Model\User;
use Override;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;
use function is_array;
use function is_string;

/**
 * @extends PersistentObjectFactory<User>
 */
final class UserFactory extends PersistentObjectFactory
{
    private const string DEFAULT_PASSWORD = 'password';

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    public static function class(): string
    {
        return User::class;
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        return [
            'username' => self::faker()->unique()->userName(),
            'displayname' => self::faker()->name(),
            'roles' => ['ROLE_USER'],
            'password' => self::DEFAULT_PASSWORD,
        ];
    }

    #[Override]
    protected function initialize(): static
    {
        return $this->afterInstantiate(function (User $user, array $attributes): void {
            // Hash the password after instantiation
            /** @var string $plainPassword */
            $plainPassword = $attributes['password'] ?? self::DEFAULT_PASSWORD;
            $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));

            // Set displayname if provided
            if (isset($attributes['displayname']) && is_string($attributes['displayname'])) {
                $user->setDisplayname($attributes['displayname']);
            }

            // Set roles if provided
            if (isset($attributes['roles']) && is_array($attributes['roles'])) {
                /** @var list<string> $roles */
                $roles = $attributes['roles'];
                $user->setRoles($roles);
            }

            // Set externalId if provided
            if (isset($attributes['externalId']) && is_string($attributes['externalId'])) {
                $user->setExternalId($attributes['externalId']);
            }
        });
    }

    public function withUsername(string $username): static
    {
        return $this->with(['username' => $username]);
    }

    public function withDisplayname(string $displayname): static
    {
        return $this->with(['displayname' => $displayname]);
    }

    public function withPassword(string $password): static
    {
        return $this->with(['password' => $password]);
    }

    public function withExternalId(string $externalId): static
    {
        return $this->with(['externalId' => $externalId]);
    }

    /**
     * @param list<string> $roles
     */
    public function withRoles(array $roles): static
    {
        return $this->with(['roles' => $roles]);
    }

    public function asAdmin(): static
    {
        return $this->withRoles(['ROLE_ADMIN']);
    }

    public function asManager(): static
    {
        return $this->withRoles(['ROLE_MANAGER']);
    }

    public function asSupport(): static
    {
        return $this->withRoles(['ROLE_SUPPORT']);
    }

    public function asActiveUser(): static
    {
        return $this->withRoles(['ROLE_ACTIVE_USER']);
    }
}
