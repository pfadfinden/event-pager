<?php

declare(strict_types=1);

namespace App\Tests\E2E\UserManagement;

use App\Tests\E2E\AbstractPantherTestCase;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\Group;

#[Group('webgui')]
final class UserInfoE2ETest extends AbstractPantherTestCase
{
    public function testCanAccessUserInfoPageWhenAuthenticated(): void
    {
        $this->loginAsActiveUser();

        $this->client->request('GET', '/userinfo');
        $this->waitForElement('h1', 5);

        self::assertSelectorTextContains('h1', 'Your Account');
    }

    public function testRedirectsToLoginWhenNotAuthenticated(): void
    {
        $this->client->request('GET', '/userinfo');

        self::assertStringContainsString('/login', $this->client->getCurrentURL());
    }

    public function testDisplaysUserDetails(): void
    {
        UserFactory::new()
            ->withUsername('profileuser')
            ->withDisplayname('Profile Test User')
            ->asActiveUser()
            ->create();

        $this->login('profileuser', 'password');

        $this->client->request('GET', '/userinfo');
        $this->waitForElement('dl', 5);

        self::assertSelectorTextContains('dd', 'profileuser');
        self::assertSelectorTextContains('dd', 'Profile Test User');
    }

    public function testDisplaysAuthenticationStatus(): void
    {
        $this->loginAsActiveUser();

        $this->client->request('GET', '/userinfo');
        $this->waitForElement('dl', 5);

        // Should show password is set (local login enabled)
        self::assertSelectorTextContains('.badge.bg-success', 'Password set');
    }

    public function testDisplaysSsoConnectedStatusForSsoUser(): void
    {
        UserFactory::new()
            ->withUsername('ssouser')
            ->withExternalId('keycloak-uuid-12345')
            ->asActiveUser()
            ->create();

        $this->login('ssouser', 'password');

        $this->client->request('GET', '/userinfo');
        $this->waitForElement('dl', 5);

        // Should show SSO is connected
        self::assertSelectorTextContains('.badge.bg-success', 'Connected');
    }

    public function testDisplaysUserRoles(): void
    {
        $this->loginAsAdmin();

        $this->client->request('GET', '/userinfo');
        $this->waitForElement('.list-group', 5);

        self::assertSelectorTextContains('.list-group-item', 'ROLE_ADMIN');
    }

    public function testShowsPasswordChangeFormForLocalUser(): void
    {
        $this->loginAsActiveUser();

        $this->client->request('GET', '/userinfo');
        $this->waitForElement('form', 5);

        // Should have password change form
        self::assertSelectorExists('input[type="password"]');
        self::assertSelectorTextContains('button[type="submit"]', 'Change Password');
    }

    public function testShowsLogoutButton(): void
    {
        $this->loginAsActiveUser();

        $this->client->request('GET', '/userinfo');
        $this->waitForElement('a.btn-outline-danger', 5);

        self::assertSelectorTextContains('a.btn-outline-danger', 'Sign out');
    }

    public function testLogoutButtonWorks(): void
    {
        $this->loginAsActiveUser();

        $this->client->request('GET', '/userinfo');
        $this->waitForElement('a.btn-outline-danger', 5);

        $this->client->clickLink('Sign out');
        $this->waitForElement('body', 5);

        // Should redirect to login page after logout
        self::assertStringContainsString('/login', $this->client->getCurrentURL());
    }

    public function testPasswordChangeValidationRequiresCurrentPassword(): void
    {
        $this->loginAsActiveUser();

        $this->client->request('GET', '/userinfo');
        $this->waitForElement('form', 5);

        // Try to submit with empty current password
        $this->client->submitForm('Change Password', [
            'form[current_password]' => '',
            'form[password][first]' => 'newpassword123',
            'form[password][second]' => 'newpassword123',
        ]);

        // HTML5 validation or server-side validation should prevent this
        // We're still on the same page
        self::assertStringContainsString('/userinfo', $this->client->getCurrentURL());
    }

    public function testPasswordChangeWithIncorrectCurrentPasswordShowsError(): void
    {
        $this->loginAsActiveUser();

        $this->client->request('GET', '/userinfo');
        $this->waitForElement('form', 5);

        $this->client->submitForm('Change Password', [
            'form[current_password]' => 'wrongpassword',
            'form[password][first]' => 'newpassword123',
            'form[password][second]' => 'newpassword123',
        ]);

        $this->waitForElement('.alert', 5);

        // Should show error message
        self::assertSelectorTextContains('.alert', 'incorrect');
    }
}
