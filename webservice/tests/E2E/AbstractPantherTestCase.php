<?php

declare(strict_types=1);

namespace App\Tests\E2E;

use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * Base class for E2E tests using Symfony Panther.
 *
 * Uses Panther's internal PHP built-in server to ensure
 * tests use the same database as the PHPUnit runner.
 */
#[Group('webgui')]
abstract class AbstractPantherTestCase extends PantherTestCase
{
    use Factories;
    use ResetDatabase;

    protected const int DEFAULT_WAIT_TIMEOUT = 5;

    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = self::createPantherClient([
            'hostname' => '127.0.0.1',
            'port' => 9080,
        ], [
            '--disable-gpu',
            '--no-sandbox',
            '--disable-dev-shm-usage',
        ]);
    }

    protected function tearDown(): void
    {
        $this->client->quit();
        parent::tearDown();
    }

    /**
     * Logs in a user via the login form.
     */
    protected function login(string $username, string $password): void
    {
        $this->client->request('GET', '/login/');
        $this->client->waitFor('form', self::DEFAULT_WAIT_TIMEOUT);
        $this->client->submitForm('Log in with local account', [
            '_username' => $username,
            '_password' => $password,
        ]);
        $this->client->waitFor('body', self::DEFAULT_WAIT_TIMEOUT);
    }

    /**
     * Creates a user with ROLE_ACTIVE_USER and logs them in.
     * ROLE_ACTIVE_USER grants ROLE_SEND which allows sending messages.
     */
    protected function loginAsActiveUser(): void
    {
        UserFactory::new()
            ->asActiveUser()
            ->withUsername('testactiveuser')
            ->create();

        $this->login('testactiveuser', 'password');
    }

    /**
     * Creates a user with ROLE_SUPPORT and logs them in.
     * ROLE_SUPPORT grants permissions to manage recipients and view users.
     */
    protected function loginAsSupport(): void
    {
        UserFactory::new()
            ->asSupport()
            ->withUsername('testsupport')
            ->create();

        $this->login('testsupport', 'password');
    }

    /**
     * Creates a user with ROLE_MANAGER and logs them in.
     * ROLE_MANAGER grants permissions to manage pagers and transports.
     */
    protected function loginAsManager(): void
    {
        UserFactory::new()
            ->asManager()
            ->withUsername('testmanager')
            ->create();

        $this->login('testmanager', 'password');
    }

    /**
     * Creates a user with ROLE_ADMIN and logs them in.
     */
    protected function loginAsAdmin(): void
    {
        UserFactory::new()
            ->asAdmin()
            ->withUsername('testadmin')
            ->create();

        $this->login('testadmin', 'password');
    }

    /**
     * Waits for an element to appear on the page.
     */
    protected function waitForElement(string $selector, int $timeout = self::DEFAULT_WAIT_TIMEOUT): void
    {
        $this->client->waitFor($selector, $timeout);
    }

    /**
     * Captures a screenshot for debugging. Useful when tests fail.
     * Screenshots are saved to /app/var/screenshots/.
     */
    protected function takeScreenshot(string $name): void
    {
        $this->client->takeScreenshot('/app/var/screenshots/'.$name.'.png');
    }

    /**
     * Gets the current page HTML for debugging.
     */
    protected function getPageHtml(): string
    {
        return $this->client->getCrawler()->html();
    }

    /**
     * Asserts that the current URL contains the given path.
     */
    protected function assertCurrentUrlContains(string $path): void
    {
        self::assertStringContainsString($path, $this->client->getCurrentURL());
    }
}
