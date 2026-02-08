<?php

declare(strict_types=1);

namespace App\Infrastructure\Security\OAuth;

use App\Core\UserManagement\Model\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use function sprintf;

/**
 * Handles OIDC Frontchannel Logout for Keycloak SSO users.
 *
 * When a user with an external ID (SSO user) logs out, they are redirected
 * to the Keycloak logout endpoint to complete the Single Logout (SLO) flow.
 */
final readonly class KeycloakLogoutSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private string $keycloakBaseUrl,
        private string $keycloakRealm,
        private string $keycloakClientId,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LogoutEvent::class => 'onLogout',
        ];
    }

    public function onLogout(LogoutEvent $event): void
    {
        $user = $event->getToken()?->getUser();

        if (!$user instanceof User) {
            return;
        }

        // Only redirect to Keycloak logout for SSO users
        if (null === $user->getExternalId()) {
            return;
        }

        $postLogoutRedirectUri = $this->urlGenerator->generate(
            'app_login',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );

        $logoutUrl = sprintf(
            '%s/realms/%s/protocol/openid-connect/logout?client_id=%s&post_logout_redirect_uri=%s',
            rtrim($this->keycloakBaseUrl, '/'),
            urlencode($this->keycloakRealm),
            urlencode($this->keycloakClientId),
            urlencode($postLogoutRedirectUri),
        );

        $event->setResponse(new RedirectResponse($logoutUrl));
    }
}
