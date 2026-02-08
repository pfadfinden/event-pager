<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Security\OAuth;

use App\Core\UserManagement\Model\User;
use App\Infrastructure\Persistence\DoctrineORM\Repository\UserRepository;
use App\Infrastructure\Security\OAuth\KeycloakUserProvider;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use function in_array;

#[CoversClass(KeycloakUserProvider::class)]
#[Group('unit')]
final class KeycloakUserProviderTest extends TestCase
{
    private UserRepository&MockObject $userRepository;
    private KeycloakUserProvider $sut;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->sut = new KeycloakUserProvider($this->userRepository);
    }

    public function testLoadUserByOAuthUserResponseCreatesNewUserWhenNotFound(): void
    {
        $response = $this->createStubOAuthResponse(
            externalId: 'kc-uuid-12345',
            username: 'johndoe',
            realName: 'John Doe',
            roles: ['ROLE_ADMIN', 'ROLE_USER', 'some-other-role'],
        );

        $this->userRepository->expects(self::once())
            ->method('findOneByExternalId')
            ->with('kc-uuid-12345')
            ->willReturn(null);

        $this->userRepository->expects(self::once())
            ->method('findOneByUsername')
            ->with('johndoe')
            ->willReturn(null);

        $this->userRepository->expects(self::once())
            ->method('save')
            ->with(self::callback(function (User $user): bool {
                return 'johndoe' === $user->getUsername()
                    && 'kc-uuid-12345' === $user->getExternalId()
                    && 'John Doe' === $user->getDisplayname()
                    && in_array('ROLE_ADMIN', $user->getRoles(), true)
                    && in_array('ROLE_USER', $user->getRoles(), true);
            }));

        $user = $this->sut->loadUserByOAuthUserResponse($response);

        self::assertInstanceOf(User::class, $user);
        self::assertSame('johndoe', $user->getUsername());
        self::assertSame('kc-uuid-12345', $user->getExternalId());
        self::assertSame('John Doe', $user->getDisplayname());
        self::assertContains('ROLE_ADMIN', $user->getRoles());
        self::assertContains('ROLE_USER', $user->getRoles()); // From Keycloak response
        self::assertNotContains('some-other-role', $user->getRoles());
    }

    public function testLoadUserByOAuthUserResponseLinksExistingUserByUsername(): void
    {
        $existingUser = new User('johndoe');
        $existingUser->setDisplayname('Old Name');

        $response = $this->createStubOAuthResponse(
            externalId: 'kc-uuid-12345',
            username: 'johndoe',
            realName: 'John Doe Updated',
            roles: ['ROLE_MANAGER'],
        );

        $this->userRepository->expects(self::once())
            ->method('findOneByExternalId')
            ->with('kc-uuid-12345')
            ->willReturn(null);

        $this->userRepository->expects(self::once())
            ->method('findOneByUsername')
            ->with('johndoe')
            ->willReturn($existingUser);

        $this->userRepository->expects(self::once())
            ->method('save')
            ->with($existingUser);

        $user = $this->sut->loadUserByOAuthUserResponse($response);

        self::assertSame($existingUser, $user);
        self::assertSame('kc-uuid-12345', $user->getExternalId());
        self::assertSame('John Doe Updated', $user->getDisplayname());
        self::assertContains('ROLE_MANAGER', $user->getRoles());
    }

    public function testLoadUserByOAuthUserResponseUpdatesExistingUserByExternalId(): void
    {
        $existingUser = new User('johndoe');
        $existingUser->setExternalId('kc-uuid-12345');
        $existingUser->setDisplayname('Old Name');
        $existingUser->setRoles(['ROLE_USER']);

        $response = $this->createStubOAuthResponse(
            externalId: 'kc-uuid-12345',
            username: 'johndoe_updated',
            realName: 'John Doe New Name',
            roles: ['ROLE_ADMIN'],
        );

        $this->userRepository->expects(self::once())
            ->method('findOneByExternalId')
            ->with('kc-uuid-12345')
            ->willReturn($existingUser);

        $this->userRepository->expects(self::never())
            ->method('findOneByUsername');

        $this->userRepository->expects(self::once())
            ->method('save')
            ->with($existingUser);

        $user = $this->sut->loadUserByOAuthUserResponse($response);

        self::assertSame($existingUser, $user);
        self::assertSame('johndoe_updated', $user->getUsername());
        self::assertSame('John Doe New Name', $user->getDisplayname());
        self::assertContains('ROLE_ADMIN', $user->getRoles());
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testLoadUserByOAuthUserResponseThrowsExceptionWhenExternalIdIsNull(): void
    {
        $response = $this->createStubOAuthResponse(
            externalId: null,
            username: 'johndoe',
            realName: 'John Doe',
            roles: [],
        );

        $this->expectException(UnsupportedUserException::class);
        $this->expectExceptionMessage('Keycloak did not return a user identifier (sub claim).');

        $this->sut->loadUserByOAuthUserResponse($response);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testLoadUserByOAuthUserResponseThrowsExceptionWhenExternalIdIsEmpty(): void
    {
        $response = $this->createStubOAuthResponse(
            externalId: '',
            username: 'johndoe',
            realName: 'John Doe',
            roles: [],
        );

        $this->expectException(UnsupportedUserException::class);
        $this->expectExceptionMessage('Keycloak did not return a user identifier (sub claim).');

        $this->sut->loadUserByOAuthUserResponse($response);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testLoadUserByOAuthUserResponseThrowsExceptionWhenUsernameIsNull(): void
    {
        $response = $this->createStubOAuthResponse(
            externalId: 'kc-uuid-12345',
            username: null,
            realName: 'John Doe',
            roles: [],
        );

        $this->expectException(UnsupportedUserException::class);
        $this->expectExceptionMessage('Keycloak did not return a username (preferred_username).');

        $this->sut->loadUserByOAuthUserResponse($response);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testLoadUserByOAuthUserResponseThrowsExceptionWhenUsernameIsEmpty(): void
    {
        $response = $this->createStubOAuthResponse(
            externalId: 'kc-uuid-12345',
            username: '',
            realName: 'John Doe',
            roles: [],
        );

        $this->expectException(UnsupportedUserException::class);
        $this->expectExceptionMessage('Keycloak did not return a username (preferred_username).');

        $this->sut->loadUserByOAuthUserResponse($response);
    }

    public function testLoadUserByOAuthUserResponseFiltersOnlyRolePrefixedRoles(): void
    {
        $response = $this->createStubOAuthResponse(
            externalId: 'kc-uuid-12345',
            username: 'johndoe',
            realName: 'John Doe',
            roles: [
                'ROLE_ADMIN',
                'ROLE_MANAGER',
                'offline_access',
                'uma_authorization',
                'default-roles-realm',
                'ROLE_SUPPORT',
            ],
        );

        $this->userRepository->method('findOneByExternalId')->willReturn(null);
        $this->userRepository->method('findOneByUsername')->willReturn(null);

        $savedUser = null;
        $this->userRepository->expects(self::once())
            ->method('save')
            ->willReturnCallback(function (User $user) use (&$savedUser): void {
                $savedUser = $user;
            });

        $user = $this->sut->loadUserByOAuthUserResponse($response);

        self::assertContains('ROLE_ADMIN', $user->getRoles());
        self::assertContains('ROLE_MANAGER', $user->getRoles());
        self::assertContains('ROLE_SUPPORT', $user->getRoles());
        self::assertNotContains('offline_access', $user->getRoles());
        self::assertNotContains('uma_authorization', $user->getRoles());
        self::assertNotContains('default-roles-realm', $user->getRoles());
    }

    public function testLoadUserByOAuthUserResponseHandlesEmptyRoles(): void
    {
        $response = $this->createStubOAuthResponse(
            externalId: 'kc-uuid-12345',
            username: 'johndoe',
            realName: 'John Doe',
            roles: [],
        );

        $this->userRepository->expects(self::once())->method('findOneByExternalId')->willReturn(null);
        $this->userRepository->expects(self::once())->method('findOneByUsername')->willReturn(null);

        $user = $this->sut->loadUserByOAuthUserResponse($response);

        // No roles from Keycloak means empty roles array
        self::assertSame([], $user->getRoles());
    }

    public function testLoadUserByOAuthUserResponseHandlesMissingResourceAccess(): void
    {
        $response = self::createStub(UserResponseInterface::class);
        $response->method('getUsername')->willReturn('kc-uuid-12345');
        $response->method('getNickname')->willReturn('johndoe');
        $response->method('getRealName')->willReturn('John Doe');
        $response->method('getData')->willReturn([]); // No resource_access key

        $this->userRepository->expects(self::once())->method('findOneByExternalId')->willReturn(null);
        $this->userRepository->expects(self::once())->method('findOneByUsername')->willReturn(null);

        $user = $this->sut->loadUserByOAuthUserResponse($response);

        self::assertSame([], $user->getRoles());
    }

    public function testRefreshUserReloadsFromDatabase(): void
    {
        $user = new User('johndoe');
        // Use reflection to set the id since it's normally auto-generated
        $reflection = new ReflectionClass($user);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setValue($user, 123);

        $refreshedUser = new User('johndoe');
        $idProperty->setValue($refreshedUser, 123);
        $refreshedUser->setDisplayname('Refreshed Name');

        $this->userRepository->expects(self::once())
            ->method('find')
            ->with(123)
            ->willReturn($refreshedUser);

        $result = $this->sut->refreshUser($user);

        self::assertSame($refreshedUser, $result);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testRefreshUserThrowsExceptionForUnsupportedUserClass(): void
    {
        $user = $this->createMock(UserInterface::class);

        $this->expectException(UnsupportedUserException::class);
        $this->expectExceptionMessageMatches('/Instances of .* are not supported/');

        $this->sut->refreshUser($user);
    }

    public function testRefreshUserThrowsExceptionWhenUserNotFoundInDatabase(): void
    {
        $user = new User('johndoe');
        $reflection = new ReflectionClass($user);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setValue($user, 999);

        $this->userRepository->expects(self::once())
            ->method('find')
            ->with(999)
            ->willReturn(null);

        $this->expectException(UnsupportedUserException::class);
        $this->expectExceptionMessage('User with ID "999" not found.');

        $this->sut->refreshUser($user);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testSupportsClassReturnsTrueForUserClass(): void
    {
        self::assertTrue($this->sut->supportsClass(User::class));
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testSupportsClassReturnsFalseForOtherClasses(): void
    {
        self::assertFalse($this->sut->supportsClass(stdClass::class));
        self::assertFalse($this->sut->supportsClass(UserInterface::class));
    }

    public function testLoadUserByIdentifierReturnsUserWhenFound(): void
    {
        $user = new User('johndoe');

        $this->userRepository->expects(self::once())
            ->method('findOneByUsername')
            ->with('johndoe')
            ->willReturn($user);

        $result = $this->sut->loadUserByIdentifier('johndoe');

        self::assertSame($user, $result);
    }

    public function testLoadUserByIdentifierThrowsExceptionWhenNotFound(): void
    {
        $this->userRepository->expects(self::once())
            ->method('findOneByUsername')
            ->with('unknown')
            ->willReturn(null);

        $this->expectException(UnsupportedUserException::class);
        $this->expectExceptionMessage('User "unknown" not found.');

        $this->sut->loadUserByIdentifier('unknown');
    }

    /**
     * @param string[] $roles
     */
    private function createStubOAuthResponse(
        ?string $externalId,
        ?string $username,
        ?string $realName,
        array $roles,
    ): UserResponseInterface {
        $response = self::createStub(UserResponseInterface::class);
        $response->method('getUsername')->willReturn($externalId);
        $response->method('getNickname')->willReturn($username);
        $response->method('getRealName')->willReturn($realName);
        $response->method('getData')->willReturn([
            'roles' => $roles,
        ]);

        return $response;
    }
}
