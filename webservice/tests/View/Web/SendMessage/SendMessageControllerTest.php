<?php

namespace App\Tests\View\Web\SendMessage;

use App\Tests\TestUtilities\FormValidationTrait;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[Group('application')]
final class SendMessageControllerTest extends WebTestCase
{
    use FormValidationTrait;

    public function testGetAndSubmitSuccess(): void
    {
        // This calls KernelTestCase::bootKernel(), and creates a
        // "client" that is acting as the browser
        $client = static::createClient();

        // Request a specific page
        $crawler = $client->request('GET', '/send');

        // Validate a successful response and some content
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Send Message');

        $crawler = $client->submitForm('Send', [
            'send_message_form[message]' => 'Hello World',
            'send_message_form[priority]' => 1,
            'send_message_form[to]' => '01J6YVHAW9G41R0C33G6CPRY85',
        ]);

        self::assertResponseRedirects('/send', 302, message: 'redirect to send page');
    }
}
