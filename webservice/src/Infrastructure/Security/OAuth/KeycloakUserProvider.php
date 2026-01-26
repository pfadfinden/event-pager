<?php

declare(strict_types=1);

namespace App\Infrastructure\Security\OAuth;

use App\Core\UserManagement\Model\User;
use App\Infrastructure\Persistence\DoctrineORM\Repository\UserRepository;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use function array_filter;
use function sprintf;
use function str_starts_with;

/**
 * Custom user provider for Keycloak OAuth authentication.
 * Creates/links users in the database and syncs displayname and roles from Keycloak.
 *
 * @implements UserProviderInterface<User>
 */
final class KeycloakUserProvider implements OAuthAwareUserProviderInterface, UserProviderInterface
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
    }

    /**
     * Loads or creates a user based on OAuth response from Keycloak.
     *
     * Flow:
     * 1. Look up user by externalId (Keycloak's `sub` claim)
     * 2. If not found, look up by username and link (migration support)
     * 3. If still not found, create new user
     * 4. Update displayname and roles from Keycloak on each login
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response): UserInterface
    {
        $externalId = $response->getUsername(); // This is the 'sub' claim (UUID)
        $username = $response->getNickname();   // preferred_username
        $displayname = $response->getRealName(); // name or realname from Keycloak

        if (null === $externalId || '' === $externalId) {
            throw new UnsupportedUserException('Keycloak did not return a user identifier (sub claim).');
        }

        if (null === $username || '' === $username) {
            throw new UnsupportedUserException('Keycloak did not return a username (preferred_username).');
        }

        // Try to find existing user by externalId
        $user = $this->userRepository->findOneByExternalId($externalId);

        if (null === $user) {
            // Try to find by username (for migrating existing users)
            $user = $this->userRepository->findOneByUsername($username);

            if (null !== $user) {
                // Link existing user to Keycloak
                $user->setExternalId($externalId);
            } else {
                // Create new user
                $user = new User($username);
                $user->setExternalId($externalId);
            }
        }

        // Always update displayname from Keycloak (source of truth)
        $user->setDisplayname($displayname);
        $user->setUsername($username);

        // Extract and sync roles from Keycloak
        $keycloakRoles = $this->extractRolesFromResponse($response);
        $user->setRoles($keycloakRoles);

        // Persist changes
        $this->userRepository->save($user);

        return $user;
    }

    /**
     * Extracts ROLE_* prefixed roles from Keycloak response.
     * Keycloak must include a roles claim in the id token.
     *
     * @return string[]
     */
    private function extractRolesFromResponse(UserResponseInterface $response): array
    {
        $data = $response->getData();

        // Keycloak stores client roles in resource_access.<client_id>.roles
        $keycloakRoles = $data['roles'] ?? [];

        // Filter to only ROLE_* prefixed roles (1:1 mapping with app roles)
        return array_values(array_filter(
            $keycloakRoles,
            static fn (string $role): bool => str_starts_with($role, 'ROLE_'),
        ));
    }

    /**
     * Refreshes the user from the database for session refresh.
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        // Reload from database
        $refreshedUser = $this->userRepository->find($user->getId());

        if (null === $refreshedUser) {
            throw new UnsupportedUserException(sprintf('User with ID "%s" not found.', $user->getId()));
        }

        return $refreshedUser;
    }

    public function supportsClass(string $class): bool
    {
        return User::class === $class || is_subclass_of($class, User::class);
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->userRepository->findOneByUsername($identifier);

        if (null === $user) {
            throw new UnsupportedUserException(sprintf('User "%s" not found.', $identifier));
        }

        return $user;
    }
}
