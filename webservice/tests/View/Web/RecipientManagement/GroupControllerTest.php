<?php

declare(strict_types=1);

namespace App\Tests\View\Web\RecipientManagement;

use App\Core\UserManagement\Model\User;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[Group('application'), Group('application.web')]
final class GroupControllerTest extends WebTestCase
{
    public function testAddGroupPageRequiresManageRole(): void
    {
        $client = static::createClient();
        $user = (new User('testuser'))->setRoles(['ROLE_VIEW_RECIPIENTS']);
        $client->loginUser($user);

        $client->request('GET', '/recipients/group/add');

        self::assertResponseStatusCodeSame(403);
    }

    public function testAddGroupPageAccessibleWithManageRole(): void
    {
        $client = static::createClient();
        $user = (new User('testuser'))->setRoles(['ROLE_MANAGE_RECIPIENT_GROUPS']);
        $client->loginUser($user);

        $client->request('GET', '/recipients/group/add');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Add Group');
    }

    public function testAddGroupFormDisplayed(): void
    {
        $client = static::createClient();
        $user = (new User('testuser'))->setRoles(['ROLE_MANAGE_RECIPIENT_GROUPS']);
        $client->loginUser($user);

        $crawler = $client->request('GET', '/recipients/group/add');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form');
        self::assertSelectorExists('input[name*="[name]"]');
    }

    public function testGroupDetailsPageNotFoundForInvalidId(): void
    {
        $client = static::createClient();
        $user = (new User('testuser'))->setRoles(['ROLE_VIEW_RECIPIENTS']);
        $client->loginUser($user);

        $client->request('GET', '/recipients/group/invalid-id');

        self::assertResponseStatusCodeSame(404);
    }

    public function testEditGroupPageRequiresManageRole(): void
    {
        $client = static::createClient();
        $user = (new User('testuser'))->setRoles(['ROLE_VIEW_RECIPIENTS']);
        $client->loginUser($user);

        $client->request('GET', '/recipients/group/01JQXYZ123456789ABCDEFG/edit');

        self::assertResponseStatusCodeSame(403);
    }
}
