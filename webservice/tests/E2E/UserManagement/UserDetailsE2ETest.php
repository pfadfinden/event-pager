<?php

declare(strict_types=1);

namespace App\Tests\E2E\UserManagement;

use App\Tests\E2E\AbstractPantherTestCase;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\Group;

#[Group('webgui')]
final class UserDetailsE2ETest extends AbstractPantherTestCase
{
    public function testCanAccessUserDetailsPageAsSupport(): void
    {
        $this->loginAsSupport();

        $targetUser = UserFactory::new()
            ->withUsername('viewableuser')
            ->withDisplayname('Viewable User')
            ->asActiveUser()
            ->create();

        $this->client->request('GET', '/admin/user/'.$targetUser->getId());
        $this->waitForElement('h1', 5);

        self::assertSelectorTextContains('h1', 'Viewable User');
    }

    public function testRedirectsToLoginWhenNotAuthenticated(): void
    {
        $targetUser = UserFactory::new()
            ->withUsername('targetuser')
            ->create();

        $this->client->request('GET', '/admin/user/'.$targetUser->getId());

        self::assertStringContainsString('/login', $this->client->getCurrentURL());
    }

    public function testAccessDeniedForRegularUser(): void
    {
        $this->loginAsActiveUser();

        $targetUser = UserFactory::new()
            ->withUsername('targetuser')
            ->create();

        $this->client->request('GET', '/admin/user/'.$targetUser->getId());

        // Should show access denied or redirect
        self::assertStringNotContainsString('/admin/user/'.$targetUser->getId(), $this->client->getCurrentURL());
    }

    public function testDisplaysUserDetails(): void
    {
        $this->loginAsSupport();

        $targetUser = UserFactory::new()
            ->withUsername('detaileduser')
            ->withDisplayname('Detailed Test User')
            ->withRoles(['ROLE_ACTIVE_USER'])
            ->create();

        $this->client->request('GET', '/admin/user/'.$targetUser->getId());
        $this->waitForElement('dl', 5);

        self::assertSelectorTextContains('dd', 'detaileduser');
        self::assertSelectorTextContains('dd', 'Detailed Test User');
    }

    public function testDisplaysAuthenticationStatus(): void
    {
        $this->loginAsSupport();

        $targetUser = UserFactory::new()
            ->withUsername('authstatususer')
            ->withExternalId('keycloak-uuid-67890')
            ->create();

        $this->client->request('GET', '/admin/user/'.$targetUser->getId());
        $this->waitForElement('dl', 5);

        // Should show SSO connected
        self::assertSelectorTextContains('.badge.bg-success', 'Connected');
        // Should also show password set
        self::assertSelectorTextContains('.badge.bg-success', 'Password set');
    }

    public function testDisplaysUserRoles(): void
    {
        $this->loginAsSupport();

        $targetUser = UserFactory::new()
            ->withUsername('roleuser')
            ->withRoles(['ROLE_MANAGER', 'ROLE_SUPPORT'])
            ->create();

        $this->client->request('GET', '/admin/user/'.$targetUser->getId());
        $this->waitForElement('.list-group', 5);

        self::assertSelectorTextContains('.list-group', 'ROLE_MANAGER');
        self::assertSelectorTextContains('.list-group', 'ROLE_SUPPORT');
    }

    public function testShowsEditButtonForManager(): void
    {
        $this->loginAsManager();

        $targetUser = UserFactory::new()
            ->withUsername('editableuser')
            ->create();

        $this->client->request('GET', '/admin/user/'.$targetUser->getId());
        $this->waitForElement('a.btn-outline-primary', 5);

        self::assertSelectorTextContains('a.btn-outline-primary', 'Edit');
    }

    public function testEditButtonNotVisibleForSupport(): void
    {
        $this->loginAsSupport();

        $targetUser = UserFactory::new()
            ->withUsername('noneditable')
            ->create();

        $this->client->request('GET', '/admin/user/'.$targetUser->getId());
        $this->waitForElement('h1', 5);

        // Support should not see edit button
        self::assertSelectorNotExists('a[href*="/edit"]');
    }

    public function testShowsDeleteButtonForManager(): void
    {
        $this->loginAsManager();

        $targetUser = UserFactory::new()
            ->withUsername('deletableuser')
            ->create();

        $this->client->request('GET', '/admin/user/'.$targetUser->getId());
        $this->waitForElement('.bg-danger', 5);

        self::assertSelectorExists('.bg-danger');
        self::assertSelectorTextContains('.bg-danger h3', 'Danger Zone');
    }

    public function testCannotDeleteOwnAccount(): void
    {
        $this->loginAsManager();

        // Get manager's user ID
        /** @var string $currentUsername */
        $currentUsername = $this->client->executeScript("
            return document.querySelector('.sidebar-user-name')?.textContent || '';
        ");

        // Navigate to own user page
        $this->client->request('GET', '/admin/user/overview');
        $this->waitForElement('table', 5);

        // Find the manager's row and click view
        $this->client->executeScript("
            const rows = document.querySelectorAll('table tbody tr');
            for (const row of rows) {
                if (row.textContent.includes('testmanager')) {
                    const viewLink = row.querySelector('a[href*=\"/admin/user/\"]');
                    if (viewLink) viewLink.click();
                    break;
                }
            }
        ");

        $this->waitForElement('.bg-danger', 5);

        // Should show warning that you cannot delete your own account
        self::assertSelectorTextContains('.bg-danger', 'cannot delete your own account');
    }

    public function testBreadcrumbNavigation(): void
    {
        $this->loginAsSupport();

        $targetUser = UserFactory::new()
            ->withUsername('breadcrumbuser')
            ->withDisplayname('Breadcrumb User')
            ->create();

        $this->client->request('GET', '/admin/user/'.$targetUser->getId());
        $this->waitForElement('.breadcrumb', 5);

        self::assertSelectorTextContains('.breadcrumb', 'Administration');
        self::assertSelectorTextContains('.breadcrumb', 'Users');
        self::assertSelectorTextContains('.breadcrumb', 'Breadcrumb User');
    }
}
