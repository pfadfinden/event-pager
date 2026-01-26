<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Persistence\DoctrineORM\Repository;

use App\Core\UserManagement\Model\User;
use App\Infrastructure\Persistence\DoctrineORM\Repository\UserRepository;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

#[CoversClass(UserRepository::class)]
final class UserRepositoryTest extends KernelTestCase
{
    use Factories;
    use ResetDatabase;

    private UserRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        $this->repository = $container->get(UserRepository::class);
    }

    public function testFindOneByExternalIdReturnsUserWhenFound(): void
    {
        // Arrange
        UserFactory::new()
            ->withUsername('johndoe')
            ->withExternalId('kc-uuid-12345')
            ->create();

        // Act
        $result = $this->repository->findOneByExternalId('kc-uuid-12345');

        // Assert
        self::assertInstanceOf(User::class, $result);
        self::assertSame('johndoe', $result->getUsername());
        self::assertSame('kc-uuid-12345', $result->getExternalId());
    }

    public function testFindOneByExternalIdReturnsNullWhenNotFound(): void
    {
        // Arrange
        UserFactory::new()
            ->withUsername('johndoe')
            ->withExternalId('kc-uuid-other')
            ->create();

        // Act
        $result = $this->repository->findOneByExternalId('kc-uuid-nonexistent');

        // Assert
        self::assertNull($result);
    }

    public function testFindOneByExternalIdReturnsNullWhenUserHasNoExternalId(): void
    {
        // Arrange
        UserFactory::new()
            ->withUsername('johndoe')
            ->create(); // No externalId set

        // Act
        $result = $this->repository->findOneByExternalId('kc-uuid-12345');

        // Assert
        self::assertNull($result);
    }

    public function testFindOneByUsernameReturnsUserWhenFound(): void
    {
        // Arrange
        UserFactory::new()
            ->withUsername('johndoe')
            ->create();

        // Act
        $result = $this->repository->findOneByUsername('johndoe');

        // Assert
        self::assertInstanceOf(User::class, $result);
        self::assertSame('johndoe', $result->getUsername());
    }

    public function testFindOneByUsernameReturnsNullWhenNotFound(): void
    {
        // Arrange
        UserFactory::new()
            ->withUsername('johndoe')
            ->create();

        // Act
        $result = $this->repository->findOneByUsername('janedoe');

        // Assert
        self::assertNull($result);
    }

    public function testSavePersistsNewUser(): void
    {
        // Arrange
        $user = new User('newuser');
        $user->setDisplayname('New User');
        $user->setExternalId('kc-new-uuid');
        $user->setPassword('hashedpassword');

        // Act
        $this->repository->save($user);

        // Assert
        $found = $this->repository->findOneByUsername('newuser');
        self::assertInstanceOf(User::class, $found);
        self::assertSame('New User', $found->getDisplayname());
        self::assertSame('kc-new-uuid', $found->getExternalId());
    }

    public function testSaveUpdatesExistingUser(): void
    {
        // Arrange
        UserFactory::new()
            ->withUsername('johndoe')
            ->withDisplayname('Old Name')
            ->create();

        // Get fresh reference from DB
        $user = $this->repository->findOneByUsername('johndoe');
        self::assertNotNull($user);

        // Act
        $user->setDisplayname('New Name');
        $user->setExternalId('kc-linked-uuid');
        $this->repository->save($user);

        // Assert - get fresh from DB
        $found = $this->repository->findOneByUsername('johndoe');
        self::assertNotNull($found);
        self::assertSame('New Name', $found->getDisplayname());
        self::assertSame('kc-linked-uuid', $found->getExternalId());
    }

    public function testDeleteRemovesUser(): void
    {
        // Arrange
        UserFactory::new()
            ->withUsername('todelete')
            ->create();

        $user = $this->repository->findOneByUsername('todelete');
        self::assertNotNull($user);

        // Act
        $this->repository->delete($user);

        // Assert
        $found = $this->repository->findOneByUsername('todelete');
        self::assertNull($found);
    }
}
