<?php

declare(strict_types=1);

namespace App\Tests\E2E\SendMessage;

use App\Tests\E2E\AbstractPantherTestCase;
use App\Tests\Factory\RecipientGroupFactory;
use PHPUnit\Framework\Attributes\Group;

#[Group('webgui')]
final class SendMessageE2ETest extends AbstractPantherTestCase
{
    public function testCanAccessSendPageWhenAuthenticated(): void
    {
        $this->loginAsActiveUser();

        $this->client->request('GET', '/send');

        self::assertSelectorTextContains('h1', 'Send Message');
    }

    public function testRedirectsToLoginWhenNotAuthenticated(): void
    {
        $this->client->request('GET', '/send');

        // Should redirect to login page
        self::assertStringContainsString('/login', $this->client->getCurrentURL());
        self::assertSelectorTextContains('h1', 'Event Pager System');
    }

    public function testCanSearchAndSelectRecipient(): void
    {
        $this->loginAsActiveUser();

        // Create a test group
        RecipientGroupFactory::new()
            ->withName('Test Security Group')
            ->create();

        $this->client->request('GET', '/send');
        $this->client->waitFor('input[placeholder="Search..."]', 5);

        // Trigger the search via JavaScript
        $this->client->executeScript("
            const searchInput = document.querySelector('input[placeholder=\"Search...\"]');
            searchInput.value = 'Test Security';
            searchInput.dispatchEvent(new Event('keyup', { bubbles: true }));
        ");

        // Wait for options to populate via AJAX
        $this->client->waitFor('select option', 5);

        // Verify the group appears in the options
        /** @var string $optionText */
        $optionText = $this->client->executeScript("
            const options = document.querySelectorAll('select option');
            return Array.from(options).map(o => o.textContent).join(',');
        ");

        self::assertStringContainsString('Test Security Group', $optionText);
    }

    public function testCanSubmitMessageWithRecipient(): void
    {
        $this->loginAsActiveUser();

        // Create a test group
        RecipientGroupFactory::new()
            ->withName('E2E Test Group')
            ->create();

        $this->client->request('GET', '/send');
        $this->client->waitFor('textarea', 5);

        // Fill in the message
        $this->client->executeScript("
            document.querySelector('textarea[name*=\"message\"]').value = 'Test message from E2E test';
        ");

        // Trigger the search and select a recipient
        $this->client->executeScript("
            const searchInput = document.querySelector('input[placeholder=\"Search...\"]');
            searchInput.value = 'E2E Test Group';
            searchInput.dispatchEvent(new Event('keyup', { bubbles: true }));
        ");

        // Wait for options to populate
        $this->client->waitFor('select option', 5);

        // Select the group by double-clicking (simulated via JavaScript)
        $this->client->executeScript("
            const options = document.querySelectorAll('select option');
            for (const option of options) {
                if (option.textContent.includes('E2E Test Group')) {
                    option.selected = true;
                    option.dispatchEvent(new MouseEvent('dblclick', { bubbles: true }));
                    break;
                }
            }
        ");

        // Wait for recipient to be added to the table
        $this->client->waitFor('table tbody tr', 5);

        // Verify recipient was added
        /** @var string $tableContent */
        $tableContent = $this->client->executeScript("
            return document.querySelector('table tbody').textContent;
        ");
        self::assertStringContainsString('E2E Test Group', $tableContent);

        // Submit the form
        $this->client->submitForm('Send');

        // Should redirect back to send page after successful submission
        $this->client->waitFor('body', 5);
        self::assertStringContainsString('/send', $this->client->getCurrentURL());
    }

    public function testFormValidationShowsErrorForEmptyMessage(): void
    {
        $this->loginAsActiveUser();

        // Create a test group
        RecipientGroupFactory::new()
            ->withName('Validation Test Group')
            ->create();

        $this->client->request('GET', '/send');
        $this->client->waitFor('textarea', 5);

        // Add a recipient but leave message empty
        $this->client->executeScript("
            const searchInput = document.querySelector('input[placeholder=\"Search...\"]');
            searchInput.value = 'Validation Test';
            searchInput.dispatchEvent(new Event('keyup', { bubbles: true }));
        ");

        $this->client->waitFor('select option', 5);

        $this->client->executeScript("
            const options = document.querySelectorAll('select option');
            for (const option of options) {
                if (option.textContent.includes('Validation Test Group')) {
                    option.selected = true;
                    option.dispatchEvent(new MouseEvent('dblclick', { bubbles: true }));
                    break;
                }
            }
        ");

        $this->client->waitFor('table tbody tr', 5);

        // Try to submit with empty message - HTML5 validation should prevent submission
        // or form should show validation error
        $messageField = $this->client->getCrawler()->filter('textarea[name*="message"]')->first();

        // Check if the field has the required attribute
        self::assertTrue(null !== $messageField->attr('required') || null !== $messageField->attr('minlength'));
    }

    public function testMultipleRecipientsCanBeSelected(): void
    {
        $this->loginAsActiveUser();

        // Create multiple test groups
        RecipientGroupFactory::new()
            ->withName('First Group')
            ->create();

        RecipientGroupFactory::new()
            ->withName('Second Group')
            ->create();

        $this->client->request('GET', '/send');
        $this->client->waitFor('textarea', 5);

        // Search and select first group
        $this->client->executeScript("
            const searchInput = document.querySelector('input[placeholder=\"Search...\"]');
            searchInput.value = 'First Group';
            searchInput.dispatchEvent(new Event('keyup', { bubbles: true }));
        ");

        $this->client->waitFor('select option', 5);

        $this->client->executeScript("
            const options = document.querySelectorAll('select option');
            for (const option of options) {
                if (option.textContent.includes('First Group')) {
                    option.selected = true;
                    option.dispatchEvent(new MouseEvent('dblclick', { bubbles: true }));
                    break;
                }
            }
        ");

        // Wait for first recipient to be added
        $this->client->waitFor('table tbody tr', 5);

        // Search and select second group
        $this->client->executeScript("
            const searchInput = document.querySelector('input[placeholder=\"Search...\"]');
            searchInput.value = 'Second Group';
            searchInput.dispatchEvent(new Event('keyup', { bubbles: true }));
        ");

        // Wait a bit for AJAX to complete and options to update
        usleep(500000); // 500ms
        $this->client->waitFor('select option', 5);

        $this->client->executeScript("
            const options = document.querySelectorAll('select option');
            for (const option of options) {
                if (option.textContent.includes('Second Group')) {
                    option.selected = true;
                    option.dispatchEvent(new MouseEvent('dblclick', { bubbles: true }));
                    break;
                }
            }
        ");

        // Wait for second recipient to be added
        usleep(500000); // 500ms

        // Verify both recipients are in the table
        /** @var int $rowCount */
        $rowCount = $this->client->executeScript("
            return document.querySelectorAll('table tbody tr').length;
        ");

        self::assertGreaterThanOrEqual(2, $rowCount, 'Expected at least 2 recipients in the table');

        /** @var string $tableContent */
        $tableContent = $this->client->executeScript("
            return document.querySelector('table tbody').textContent;
        ");

        self::assertStringContainsString('First Group', $tableContent);
        self::assertStringContainsString('Second Group', $tableContent);
    }
}
