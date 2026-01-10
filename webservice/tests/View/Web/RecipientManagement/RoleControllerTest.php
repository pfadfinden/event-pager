<?php

declare(strict_types=1);

namespace App\Tests\View\Web\RecipientManagement;

use App\Core\UserManagement\Model\User;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[Group('application'), Group('application.web')]
final class RoleControllerTest extends WebTestCase
{
    public function testAddRolePageRequiresManageRole(): void
    {
        $client = static::createClient();
        $user = (new User('testuser'))->setRoles(['ROLE_VIEW_RECIPIENTS']);
        $client->loginUser($user);

        $client->request('GET', '/recipients/role/add');

        self::assertResponseStatusCodeSame(403);
    }

    public function testAddRolePageAccessibleWithManageRole(): void
    {
        $client = static::createClient();
        $user = (new User('testuser'))->setRoles(['ROLE_MANAGE_RECIPIENT_ROLES']);
        $client->loginUser($user);

        $client->request('GET', '/recipients/role/add');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Add Role');
    }

    public function testAddRoleFormDisplayed(): void
    {
        $client = static::createClient();
        $user = (new User('testuser'))->setRoles(['ROLE_MANAGE_RECIPIENT_ROLES']);
        $client->loginUser($user);

        $crawler = $client->request('GET', '/recipients/role/add');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form');
        self::assertSelectorExists('input[name*="[name]"]');
    }

    public function testRoleDetailsPageNotFoundForInvalidId(): void
    {
        $client = static::createClient();
        $user = (new User('testuser'))->setRoles(['ROLE_VIEW_RECIPIENTS']);
        $client->loginUser($user);

        $client->request('GET', '/recipients/role/invalid-id');

        self::assertResponseStatusCodeSame(404);
    }

    public function testEditRolePageRequiresManageRole(): void
    {
        $client = static::createClient();
        $user = (new User('testuser'))->setRoles(['ROLE_VIEW_RECIPIENTS']);
        $client->loginUser($user);

        $client->request('GET', '/recipients/role/01JQXYZ123456789ABCDEFG/edit');

        self::assertResponseStatusCodeSame(403);
    }
}
