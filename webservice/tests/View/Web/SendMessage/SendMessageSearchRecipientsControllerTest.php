<?php

declare(strict_types=1);

namespace App\Tests\View\Web\SendMessage;

use App\Tests\TestUtilities\FormValidationTrait;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Uid\Ulid;
use Zenstruck\Foundry\Test\ResetDatabase;

#[Group('application'), Group('application.web')]
final class SendMessageSearchRecipientsControllerTest extends WebTestCase
{
    use FormValidationTrait;
    use ResetDatabase;

    public function testReturnsMatchingRecipients(): void
    {
        $client = static::createClient();
        $this->addMockData();

        $client->jsonRequest('GET', '/send/_searchRecipients?search=wOrld');

        self::assertResponseIsSuccessful();
        self::assertResponseFormatSame('json');

        $content = $client->getResponse()->getContent();
        self::assertNotFalse($content);
        self::assertJsonStringEqualsJsonString(
            '[{"id": "01JT89K39XWEDW8DWWKYGBQR3V","name": "👥 Hello World","type": "GROUP"}]',
            $content
        );
    }

    public function testReturnsEmptyWhenNoMatchesExist(): void
    {
        $client = static::createClient();
        $this->addMockData();

        $client->jsonRequest('GET', '/send/_searchRecipients?search=Test');

        self::assertResponseIsSuccessful();
        self::assertResponseFormatSame('json');

        $content = $client->getResponse()->getContent();
        self::assertNotFalse($content);
        self::assertJsonStringEqualsJsonString(
            '[]',
            $content
        );
    }

    public function testReturnsAllWhenQueryIsEmpty(): void
    {
        $client = static::createClient();
        $this->addMockData();

        $client->jsonRequest('GET', '/send/_searchRecipients?search=');

        self::assertResponseIsSuccessful();
        self::assertResponseFormatSame('json');

        $content = $client->getResponse()->getContent();
        self::assertNotFalse($content);
        self::assertJsonStringEqualsJsonString(
            '[{"id": "01JT89K39XWEDW8DWWKYGBQR3V","name": "👥 Hello World","type": "GROUP"}]',
            $content
        );
    }

    protected function addMockData(): void
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $em->persist(new \App\Core\MessageRecipient\Model\Group('Hello World', Ulid::fromString('01JT89K39XWEDW8DWWKYGBQR3V')));
        $em->flush();
        $em->clear();
    }
}
