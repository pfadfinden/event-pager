<?php

declare(strict_types=1);

namespace App\Tests\View\Web\RecipientManagement;

use App\Core\UserManagement\Model\User;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[Group('application'), Group('application.web')]
final class RecipientOverviewControllerTest extends WebTestCase
{
    public function testAccessDeniedWithoutAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/recipients');

        self::assertResponseRedirects('/login');
    }

    public function testAccessDeniedWithoutViewRecipientsRole(): void
    {
        $client = static::createClient();
        $user = (new User('testuser'))->setRoles(['ROLE_USER']);
        $client->loginUser($user);

        $client->request('GET', '/recipients');

        self::assertResponseStatusCodeSame(403);
    }

    public function testAccessGrantedWithViewRecipientsRole(): void
    {
        $client = static::createClient();
        $user = (new User('testuser'))->setRoles(['ROLE_VIEW_RECIPIENTS']);
        $client->loginUser($user);

        $client->request('GET', '/recipients');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Recipient Management');
    }

    public function testPageContainsRecipientList(): void
    {
        $client = static::createClient();
        $user = (new User('testuser'))->setRoles(['ROLE_VIEW_RECIPIENTS']);
        $client->loginUser($user);

        $crawler = $client->request('GET', '/recipients');

        self::assertResponseIsSuccessful();
        // The page should contain the live component
        self::assertSelectorExists('[data-poll]');
    }

    public function testAddButtonsVisibleWithManageRoles(): void
    {
        $client = static::createClient();
        $user = (new User('testuser'))->setRoles(['ROLE_MANAGE_RECIPIENT_INDIVIDUALS', 'ROLE_MANAGE_RECIPIENT_GROUPS', 'ROLE_MANAGE_RECIPIENT_ROLES']);
        $client->loginUser($user);

        $crawler = $client->request('GET', '/recipients');

        self::assertResponseIsSuccessful();
        // Should have add buttons for persons, groups, and roles
        self::assertSelectorExists('a[href="/recipients/person/add"]');
        self::assertSelectorExists('a[href="/recipients/group/add"]');
        self::assertSelectorExists('a[href="/recipients/role/add"]');
    }

    public function testAddButtonsHiddenWithoutManageRoles(): void
    {
        $client = static::createClient();
        $user = (new User('testuser'))->setRoles(['ROLE_VIEW_RECIPIENTS']);
        $client->loginUser($user);

        $crawler = $client->request('GET', '/recipients');

        self::assertResponseIsSuccessful();
        // Add buttons should not be visible
        self::assertSelectorNotExists('a[href="/recipients/person/add"]');
        self::assertSelectorNotExists('a[href="/recipients/group/add"]');
        self::assertSelectorNotExists('a[href="/recipients/role/add"]');
    }
}
