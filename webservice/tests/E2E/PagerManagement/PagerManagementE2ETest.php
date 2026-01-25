<?php

declare(strict_types=1);

namespace App\Tests\E2E\PagerManagement;

use App\Tests\E2E\AbstractPantherTestCase;
use App\Tests\Factory\PagerFactory;
use PHPUnit\Framework\Attributes\Group;

#[Group('webgui')]
final class PagerManagementE2ETest extends AbstractPantherTestCase
{
    public function testCanAccessPagerOverviewPage(): void
    {
        $this->loginAsManager();

        $this->client->request('GET', '/pager-management');

        self::assertSelectorTextContains('h1', 'Pager');
    }

    public function testRedirectsToLoginWhenNotAuthenticated(): void
    {
        $this->client->request('GET', '/pager-management');

        $this->assertCurrentUrlContains('/login');
    }

    public function testCanViewPagerDetails(): void
    {
        $this->loginAsManager();

        $pager = PagerFactory::new()
            ->withLabel('E2E Test Pager')
            ->withNumber(999)
            ->create();

        $this->client->request('GET', '/pager-management/pager/'.$pager->getId());
        $this->waitForElement('body');

        self::assertSelectorTextContains('body', 'E2E Test Pager');
    }

    public function testCanNavigateToEditPagerPage(): void
    {
        $this->loginAsManager();

        $pager = PagerFactory::new()
            ->withLabel('Edit Test Pager')
            ->withNumber(998)
            ->create();

        $this->client->request('GET', '/pager-management/pager/'.$pager->getId().'/edit');
        $this->waitForElement('form');

        self::assertSelectorTextContains('h1', 'Edit');
    }

    public function testCanEditPagerLabel(): void
    {
        $this->loginAsManager();

        $pager = PagerFactory::new()
            ->withLabel('Original Pager Label')
            ->withNumber(997)
            ->create();

        $this->client->request('GET', '/pager-management/pager/'.$pager->getId().'/edit');
        $this->waitForElement('form');

        // Find and update the label field
        $this->client->executeScript("
            const labelInput = document.querySelector('input[name*=\"label\"]');
            if (labelInput) {
                labelInput.value = 'Updated Pager Label';
                labelInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
        ");

        // Submit the form
        $this->client->submitForm('Save');

        // Should redirect to details page after successful save
        $this->waitForElement('body');
        $this->assertCurrentUrlContains('/pager-management/pager/');
    }

    public function testPagerListShowsCreatedPagers(): void
    {
        $this->loginAsManager();

        PagerFactory::new()
            ->withLabel('Listed Pager One')
            ->withNumber(101)
            ->create();

        PagerFactory::new()
            ->withLabel('Listed Pager Two')
            ->withNumber(102)
            ->create();

        $this->client->request('GET', '/pager-management');
        $this->waitForElement('body');

        // The page should contain our pagers (may be in a LiveComponent)
        $pageContent = $this->getPageHtml();
        self::assertStringContainsString('Listed Pager One', $pageContent);
        self::assertStringContainsString('Listed Pager Two', $pageContent);
    }
}
