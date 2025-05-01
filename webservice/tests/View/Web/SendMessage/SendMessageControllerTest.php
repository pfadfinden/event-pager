<?php

declare(strict_types=1);

namespace App\Tests\View\Web\SendMessage;

use App\Tests\TestUtilities\FormValidationTrait;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

#[Group('application'), Group('application.web')]
final class SendMessageControllerTest extends WebTestCase
{
    use FormValidationTrait;
    use ResetDatabase;

    public function testGetAndSubmitSuccess(): void
    {
        self::markTestSkipped('WebTestCase doesnt execute javascript, which is needed to add recipients');

        // This calls KernelTestCase::bootKernel(), and creates a
        // "client" that is acting as the browser
        // $client = static::createClient();

        // Request a specific page
        // $crawler = $client->request('GET', '/send');

        // Validate a successful response and some content
        // self::assertResponseIsSuccessful();
        // self::assertSelectorTextContains('h1', 'Send Message');

        // $crawler = $client->submitForm('Send', [
        //    'send_message_form[message]' => 'Hello World',
        //    'send_message_form[priority]' => 1,
        // ]);

        // self::assertResponseRedirects('/send', 302, message: 'redirect to send page');
    }
}
