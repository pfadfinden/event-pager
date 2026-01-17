<?php

declare(strict_types=1);

namespace App\Tests\View\Web;

use Symfony\Component\HttpFoundation\Request;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[Group('application'), Group('application.web')]
final class LoginControllerTest extends WebTestCase
{
    public function testCanAssertTrue(): void
    {
        $client = self::createClient();

        // Request a specific page
        $crawler = $client->request(Request::METHOD_GET, '/login/');

        // Validate a successful response and some content
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Event Pager System');

        /*$crawler = $client->submitForm('Log in with local account',
            ['_username' => 'admin', '_password' => 'admin']
        );

        self::assertResponseIsSuccessful();*/
    }
}
