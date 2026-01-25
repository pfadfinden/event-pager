<?php

declare(strict_types=1);

namespace App\Tests\E2E\RecipientManagement;

use App\Tests\E2E\AbstractPantherTestCase;
use App\Tests\Factory\RecipientGroupFactory;
use App\Tests\Factory\RecipientPersonFactory;
use PHPUnit\Framework\Attributes\Group;

#[Group('webgui')]
final class RecipientManagementE2ETest extends AbstractPantherTestCase
{
    public function testCanAccessRecipientsOverviewPage(): void
    {
        $this->loginAsSupport();

        $this->client->request('GET', '/recipients');

        self::assertSelectorTextContains('h1', 'Recipients');
    }

    public function testRedirectsToLoginWhenNotAuthenticated(): void
    {
        $this->client->request('GET', '/recipients');

        $this->assertCurrentUrlContains('/login');
    }

    public function testCanNavigateToEditPersonPage(): void
    {
        $this->loginAsSupport();

        $person = RecipientPersonFactory::new()
            ->with(['name' => 'E2E Test Person'])
            ->create();

        $this->client->request('GET', '/recipients/person/'.$person->getId().'/edit');
        $this->waitForElement('form');

        self::assertSelectorTextContains('h1', 'Edit');
    }

    public function testCanEditPersonName(): void
    {
        $this->loginAsSupport();

        $person = RecipientPersonFactory::new()
            ->with(['name' => 'Original Name'])
            ->create();

        $this->client->request('GET', '/recipients/person/'.$person->getId().'/edit');
        $this->waitForElement('form');

        // Find and update the name field
        $this->client->executeScript("
            const nameInput = document.querySelector('input[name*=\"name\"]');
            if (nameInput) {
                nameInput.value = 'Updated E2E Name';
                nameInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
        ");

        // Submit the form
        $this->client->submitForm('Save');

        // Should redirect to details page after successful save
        $this->waitForElement('body');
        $this->assertCurrentUrlContains('/recipients/person/');
    }

    public function testCanNavigateToEditGroupPage(): void
    {
        $this->loginAsSupport();

        $group = RecipientGroupFactory::new()
            ->withName('E2E Test Group')
            ->create();

        $this->client->request('GET', '/recipients/group/'.$group->getId().'/edit');
        $this->waitForElement('form');

        self::assertSelectorTextContains('h1', 'Edit');
    }

    public function testCanEditGroupName(): void
    {
        $this->loginAsSupport();

        $group = RecipientGroupFactory::new()
            ->withName('Original Group Name')
            ->create();

        $this->client->request('GET', '/recipients/group/'.$group->getId().'/edit');
        $this->waitForElement('form');

        // Find and update the name field
        $this->client->executeScript("
            const nameInput = document.querySelector('input[name*=\"name\"]');
            if (nameInput) {
                nameInput.value = 'Updated Group Name';
                nameInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
        ");

        // Submit the form
        $this->client->submitForm('Save');

        // Should redirect to details page after successful save
        $this->waitForElement('body');
        $this->assertCurrentUrlContains('/recipients/group/');
    }

    public function testCanViewPersonDetails(): void
    {
        $this->loginAsSupport();

        $person = RecipientPersonFactory::new()
            ->with(['name' => 'Detail View Person'])
            ->create();

        $this->client->request('GET', '/recipients/person/'.$person->getId());
        $this->waitForElement('body');

        self::assertSelectorTextContains('body', 'Detail View Person');
    }

    public function testCanViewGroupDetails(): void
    {
        $this->loginAsSupport();

        $group = RecipientGroupFactory::new()
            ->withName('Detail View Group')
            ->create();

        $this->client->request('GET', '/recipients/group/'.$group->getId());
        $this->waitForElement('body');

        self::assertSelectorTextContains('body', 'Detail View Group');
    }
}
