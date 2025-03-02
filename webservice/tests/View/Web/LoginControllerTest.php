<?php

declare(strict_types=1);

namespace App\Tests\View\Web;

use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[Group('application'), Group('application.web')]
final class LoginControllerTest extends WebTestCase
{
    public function testCanAssertTrue(): void
    {
        $client = static::createClient();

        // Request a specific page
        $crawler = $client->request('GET', '/login/');

        // Validate a successful response and some content
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Login');
    }
}
