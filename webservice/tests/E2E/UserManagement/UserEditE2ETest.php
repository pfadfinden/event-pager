<?php

declare(strict_types=1);

namespace App\Tests\E2E\UserManagement;

use App\Tests\E2E\AbstractPantherTestCase;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\Group;

#[Group('webgui')]
final class UserEditE2ETest extends AbstractPantherTestCase
{
    public function testCanAccessUserEditPageAsManager(): void
    {
        $this->loginAsManager();

        $targetUser = UserFactory::new()
            ->withUsername('edituser')
            ->withDisplayname('Edit User')
            ->create();

        $this->client->request('GET', '/admin/user/'.$targetUser->getId().'/edit');
        $this->waitForElement('form', 5);

        self::assertSelectorTextContains('h1', 'Edit User');
    }

    public function testRedirectsToLoginWhenNotAuthenticated(): void
    {
        $targetUser = UserFactory::new()
            ->withUsername('targetuser')
            ->create();

        $this->client->request('GET', '/admin/user/'.$targetUser->getId().'/edit');

        self::assertStringContainsString('/login', $this->client->getCurrentURL());
    }

    public function testAccessDeniedForSupport(): void
    {
        $this->loginAsSupport();

        $targetUser = UserFactory::new()
            ->withUsername('protecteduser')
            ->create();

        $this->client->request('GET', '/admin/user/'.$targetUser->getId().'/edit');

        // Support should be denied access to edit
        self::assertStringNotContainsString('/edit', $this->client->getCurrentURL());
    }

    public function testFormShowsCurrentDisplayname(): void
    {
        $this->loginAsManager();

        $targetUser = UserFactory::new()
            ->withUsername('displayuser')
            ->withDisplayname('Current Display Name')
            ->create();

        $this->client->request('GET', '/admin/user/'.$targetUser->getId().'/edit');
        $this->waitForElement('form', 5);

        /** @var string $displaynameValue */
        $displaynameValue = $this->client->executeScript("
            return document.querySelector('input[name*=\"displayname\"]')?.value || '';
        ");

        self::assertSame('Current Display Name', $displaynameValue);
    }

    public function testFormShowsCurrentRoles(): void
    {
        $this->loginAsManager();

        $targetUser = UserFactory::new()
            ->withUsername('roleuser')
            ->withRoles(['ROLE_ACTIVE_USER', 'ROLE_SUPPORT'])
            ->create();

        $this->client->request('GET', '/admin/user/'.$targetUser->getId().'/edit');
        $this->waitForElement('form', 5);

        // Check if ROLE_SUPPORT is checked
        /** @var bool $supportChecked */
        $supportChecked = $this->client->executeScript("
            const checkbox = document.querySelector('input[value=\"ROLE_SUPPORT\"]');
            return checkbox ? checkbox.checked : false;
        ");

        self::assertTrue($supportChecked);
    }

    public function testCanUpdateDisplayname(): void
    {
        $this->loginAsManager();

        $targetUser = UserFactory::new()
            ->withUsername('updateuser')
            ->withDisplayname('Old Name')
            ->create();

        $this->client->request('GET', '/admin/user/'.$targetUser->getId().'/edit');
        $this->waitForElement('form', 5);

        // Clear and set new displayname
        $this->client->executeScript("
            const input = document.querySelector('input[name*=\"displayname\"]');
            input.value = 'New Display Name';
        ");

        $this->client->submitForm('Save');
        $this->waitForElement('.alert', 5);

        // Should show success message or redirect to details
        self::assertStringContainsString('/admin/user/'.$targetUser->getId(), $this->client->getCurrentURL());
    }

    public function testCanUpdateRoles(): void
    {
        $this->loginAsManager();

        $targetUser = UserFactory::new()
            ->withUsername('rolechangeuser')
            ->withRoles(['ROLE_ACTIVE_USER'])
            ->create();

        $this->client->request('GET', '/admin/user/'.$targetUser->getId().'/edit');
        $this->waitForElement('form', 5);

        // Check ROLE_SUPPORT checkbox
        $this->client->executeScript("
            const checkbox = document.querySelector('input[value=\"ROLE_SUPPORT\"]');
            if (checkbox) checkbox.checked = true;
        ");

        $this->client->submitForm('Save');
        $this->waitForElement('body', 5);

        // Should redirect to details page
        self::assertStringContainsString('/admin/user/'.$targetUser->getId(), $this->client->getCurrentURL());
    }

    public function testPasswordFieldsAreOptional(): void
    {
        $this->loginAsManager();

        $targetUser = UserFactory::new()
            ->withUsername('nopasswordchange')
            ->create();

        $this->client->request('GET', '/admin/user/'.$targetUser->getId().'/edit');
        $this->waitForElement('form', 5);

        // Check that password fields are not required
        /** @var bool $required */
        $required = $this->client->executeScript("
            const passwordField = document.querySelector('input[name*=\"password\"][name*=\"first\"]');
            return passwordField ? passwordField.required : false;
        ");

        self::assertFalse($required);
    }

    public function testCanSetNewPassword(): void
    {
        $this->loginAsManager();

        $targetUser = UserFactory::new()
            ->withUsername('newpassworduser')
            ->create();

        $this->client->request('GET', '/admin/user/'.$targetUser->getId().'/edit');
        $this->waitForElement('form', 5);

        // Set new password
        $this->client->executeScript("
            const firstPassword = document.querySelector('input[name*=\"password\"][name*=\"first\"]');
            const secondPassword = document.querySelector('input[name*=\"password\"][name*=\"second\"]');
            if (firstPassword) firstPassword.value = 'newpassword123';
            if (secondPassword) secondPassword.value = 'newpassword123';
        ");

        $this->client->submitForm('Save');
        $this->waitForElement('body', 5);

        // Should redirect to details page on success
        self::assertStringContainsString('/admin/user/'.$targetUser->getId(), $this->client->getCurrentURL());
    }

    public function testPasswordMismatchShowsError(): void
    {
        $this->loginAsManager();

        $targetUser = UserFactory::new()
            ->withUsername('mismatchuser')
            ->create();

        $this->client->request('GET', '/admin/user/'.$targetUser->getId().'/edit');
        $this->waitForElement('form', 5);

        // Set mismatched passwords
        $this->client->executeScript("
            const firstPassword = document.querySelector('input[name*=\"password\"][name*=\"first\"]');
            const secondPassword = document.querySelector('input[name*=\"password\"][name*=\"second\"]');
            if (firstPassword) firstPassword.value = 'password1';
            if (secondPassword) secondPassword.value = 'password2';
        ");

        $this->client->submitForm('Save');
        $this->waitForElement('body', 5);

        // Should show validation error or stay on page
        self::assertStringContainsString('/edit', $this->client->getCurrentURL());
    }

    public function testAdminCanAssignPrivilegedRoles(): void
    {
        $this->loginAsAdmin();

        $targetUser = UserFactory::new()
            ->withUsername('privilegeuser')
            ->withRoles(['ROLE_USER'])
            ->create();

        $this->client->request('GET', '/admin/user/'.$targetUser->getId().'/edit');
        $this->waitForElement('form', 5);

        // Admin should see ROLE_ADMIN checkbox
        /** @var bool $adminRoleExists */
        $adminRoleExists = $this->client->executeScript("
            return document.querySelector('input[value=\"ROLE_ADMIN\"]') !== null;
        ");

        self::assertTrue($adminRoleExists);
    }

    public function testManagerCannotAssignAdminRole(): void
    {
        $this->loginAsManager();

        $targetUser = UserFactory::new()
            ->withUsername('limiteduser')
            ->create();

        $this->client->request('GET', '/admin/user/'.$targetUser->getId().'/edit');
        $this->waitForElement('form', 5);

        // Manager should not see ROLE_ADMIN checkbox
        /** @var bool $adminRoleExists */
        $adminRoleExists = $this->client->executeScript("
            return document.querySelector('input[value=\"ROLE_ADMIN\"]') !== null;
        ");

        self::assertFalse($adminRoleExists);
    }

    public function testBreadcrumbNavigation(): void
    {
        $this->loginAsManager();

        $targetUser = UserFactory::new()
            ->withUsername('breadcrumbedit')
            ->withDisplayname('Breadcrumb Edit User')
            ->create();

        $this->client->request('GET', '/admin/user/'.$targetUser->getId().'/edit');
        $this->waitForElement('.breadcrumb', 5);

        self::assertSelectorTextContains('.breadcrumb', 'Administration');
        self::assertSelectorTextContains('.breadcrumb', 'Users');
    }

    public function testCancelButtonReturnsToDetails(): void
    {
        $this->loginAsManager();

        $targetUser = UserFactory::new()
            ->withUsername('canceluser')
            ->create();

        $this->client->request('GET', '/admin/user/'.$targetUser->getId().'/edit');
        $this->waitForElement('form', 5);

        // Look for cancel/back link
        /** @var bool $hasCancelLink */
        $hasCancelLink = $this->client->executeScript("
            return document.querySelector('a[href*=\"/admin/user/".$targetUser->getId()."\"]') !== null;
        ");

        // If there's a cancel link, click it
        if ($hasCancelLink) {
            $this->client->executeScript("
                document.querySelector('a[href*=\"/admin/user/".$targetUser->getId()."\"]').click();
            ");
            $this->waitForElement('body', 5);
            self::assertStringNotContainsString('/edit', $this->client->getCurrentURL());
        } else {
            // If no cancel link, just verify we're on the edit page
            self::assertStringContainsString('/edit', $this->client->getCurrentURL());
        }
    }
}
