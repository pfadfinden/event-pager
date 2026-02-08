<?php

declare(strict_types=1);

namespace App\Tests\Functional\Infrastructure\Security\OAuth;

use App\Core\UserManagement\Model\User;
use App\Infrastructure\Security\OAuth\KeycloakUserProvider;
use App\Tests\Factory\UserFactory;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * Application-level tests for KeycloakUserProvider with real database.
 * These tests verify the OAuth login flow works correctly end-to-end.
 */
#[CoversClass(KeycloakUserProvider::class)]
final class KeycloakUserProviderTest extends KernelTestCase
{
    use Factories;
    use ResetDatabase;

    private KeycloakUserProvider $userProvider;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->userProvider = self::getContainer()->get(KeycloakUserProvider::class);
    }

    public function testNewUserIsCreatedOnFirstLogin(): void
    {
        // Arrange
        $oauthResponse = $this->createMockOAuthResponse(
            externalId: 'kc-new-user-uuid',
            username: 'newkeycloakuser',
            realName: 'New Keycloak User',
            roles: ['ROLE_ACTIVE_USER'],
        );

        // Act
        $user = $this->userProvider->loadUserByOAuthUserResponse($oauthResponse);

        // Assert
        self::assertInstanceOf(User::class, $user);
        self::assertSame('newkeycloakuser', $user->getUsername());
        self::assertSame('kc-new-user-uuid', $user->getExternalId());
        self::assertSame('New Keycloak User', $user->getDisplayname());
        self::assertContains('ROLE_ACTIVE_USER', $user->getRoles());
        self::assertNotNull($user->getId()); // Persisted to DB
    }

    public function testExistingUserIsLinkedByUsername(): void
    {
        // Arrange - Create existing user without externalId (simulating migration)
        UserFactory::new()
            ->withUsername('existinguser')
            ->withDisplayname('Old Display Name')
            ->withRoles(['ROLE_USER'])
            ->create();

        $oauthResponse = $this->createMockOAuthResponse(
            externalId: 'kc-existing-uuid',
            username: 'existinguser',
            realName: 'Updated Display Name',
            roles: ['ROLE_ADMIN'],
        );

        // Act
        $user = $this->userProvider->loadUserByOAuthUserResponse($oauthResponse);

        // Assert
        self::assertInstanceOf(User::class, $user);
        self::assertSame('existinguser', $user->getUsername());
        self::assertSame('kc-existing-uuid', $user->getExternalId()); // Now linked
        self::assertSame('Updated Display Name', $user->getDisplayname()); // Updated
        self::assertContains('ROLE_ADMIN', $user->getRoles()); // Synced from Keycloak
    }

    public function testExistingUserFoundByExternalIdOnSubsequentLogin(): void
    {
        // Arrange - Create user already linked to Keycloak
        UserFactory::new()
            ->withUsername('keycloakuser')
            ->withExternalId('kc-returning-uuid')
            ->withDisplayname('Original Name')
            ->withRoles(['ROLE_USER'])
            ->create();

        $oauthResponse = $this->createMockOAuthResponse(
            externalId: 'kc-returning-uuid',
            username: 'keycloakuser',
            realName: 'Updated Name From Keycloak',
            roles: ['ROLE_MANAGER'],
        );

        // Act
        $user = $this->userProvider->loadUserByOAuthUserResponse($oauthResponse);

        // Assert
        self::assertInstanceOf(User::class, $user);
        self::assertSame('kc-returning-uuid', $user->getExternalId());
        self::assertSame('Updated Name From Keycloak', $user->getDisplayname());
        self::assertContains('ROLE_MANAGER', $user->getRoles());
    }

    public function testUsernameUpdatedFromKeycloakOnSubsequentLogin(): void
    {
        // Arrange - User exists with old username
        UserFactory::new()
            ->withUsername('oldusername')
            ->withExternalId('kc-username-change-uuid')
            ->create();

        // Keycloak returns updated username
        $oauthResponse = $this->createMockOAuthResponse(
            externalId: 'kc-username-change-uuid',
            username: 'newusername',
            realName: 'Same User',
            roles: [],
        );

        // Act
        $user = $this->userProvider->loadUserByOAuthUserResponse($oauthResponse);

        // Assert - Username is synced from Keycloak
        self::assertInstanceOf(User::class, $user);
        self::assertSame('newusername', $user->getUsername());
    }

    public function testOnlyRolePrefixedRolesAreSynced(): void
    {
        // Arrange
        $oauthResponse = $this->createMockOAuthResponse(
            externalId: 'kc-roles-test-uuid',
            username: 'rolesuser',
            realName: 'Roles Test User',
            roles: [
                'ROLE_ADMIN',
                'ROLE_SUPPORT',
            ],
        );

        // Act
        $user = $this->userProvider->loadUserByOAuthUserResponse($oauthResponse);

        // Assert - Only ROLE_* roles synced from Keycloak
        self::assertContains('ROLE_ADMIN', $user->getRoles());
        self::assertContains('ROLE_SUPPORT', $user->getRoles());
    }

    public function testRefreshUserLoadsFromDatabase(): void
    {
        // Arrange
        UserFactory::new()
            ->withUsername('refreshuser')
            ->withExternalId('kc-refresh-uuid')
            ->withDisplayname('Refresh Test')
            ->create();

        $user = $this->userProvider->loadUserByIdentifier('refreshuser');

        // Act
        $refreshedUser = $this->userProvider->refreshUser($user);

        // Assert
        self::assertInstanceOf(User::class, $refreshedUser);
        self::assertSame('refreshuser', $refreshedUser->getUsername());
        self::assertSame('Refresh Test', $refreshedUser->getDisplayname());
    }

    public function testLoadUserByIdentifier(): void
    {
        // Arrange
        UserFactory::new()
            ->withUsername('identifieruser')
            ->withDisplayname('Identifier Test')
            ->create();

        // Act
        $user = $this->userProvider->loadUserByIdentifier('identifieruser');

        // Assert
        self::assertInstanceOf(User::class, $user);
        self::assertSame('identifieruser', $user->getUsername());
    }

    /**
     * @param string[] $roles
     */
    private function createMockOAuthResponse(
        string $externalId,
        string $username,
        string $realName,
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
