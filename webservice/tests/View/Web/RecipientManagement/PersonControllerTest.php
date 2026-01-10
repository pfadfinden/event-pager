<?php

declare(strict_types=1);

namespace App\Tests\View\Web\RecipientManagement;

use App\Core\UserManagement\Model\User;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[Group('application'), Group('application.web')]
final class PersonControllerTest extends WebTestCase
{
    public function testAddPersonPageRequiresManageRole(): void
    {
        $client = static::createClient();
        $user = (new User('testuser'))->setRoles(['ROLE_VIEW_RECIPIENTS']);
        $client->loginUser($user);

        $client->request('GET', '/recipients/person/add');

        self::assertResponseStatusCodeSame(403);
    }

    public function testAddPersonPageAccessibleWithManageRole(): void
    {
        $client = static::createClient();
        $user = (new User('testuser'))->setRoles(['ROLE_MANAGE_RECIPIENT_INDIVIDUALS']);
        $client->loginUser($user);

        $client->request('GET', '/recipients/person/add');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Add Person');
    }

    public function testAddPersonFormDisplayed(): void
    {
        $client = static::createClient();
        $user = (new User('testuser'))->setRoles(['ROLE_MANAGE_RECIPIENT_INDIVIDUALS']);
        $client->loginUser($user);

        $crawler = $client->request('GET', '/recipients/person/add');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form');
        self::assertSelectorExists('input[name*="[name]"]');
    }

    public function testPersonDetailsPageNotFoundForInvalidId(): void
    {
        $client = static::createClient();
        $user = (new User('testuser'))->setRoles(['ROLE_VIEW_RECIPIENTS']);
        $client->loginUser($user);

        $client->request('GET', '/recipients/person/invalid-id');

        self::assertResponseStatusCodeSame(404);
    }

    public function testEditPersonPageRequiresManageRole(): void
    {
        $client = static::createClient();
        $user = (new User('testuser'))->setRoles(['ROLE_VIEW_RECIPIENTS']);
        $client->loginUser($user);

        $client->request('GET', '/recipients/person/01JQXYZ123456789ABCDEFG/edit');

        self::assertResponseStatusCodeSame(403);
    }
}
