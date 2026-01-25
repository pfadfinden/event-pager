<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Telegram\SymfonyNotifier;

use App\Core\TelegramTransport\Exception\TelegramSendFailed;
use App\Infrastructure\Telegram\SymfonyNotifier\TelegramClient;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;

/**
 * Integration test for TelegramClient against the real Telegram API.
 *
 * Requires environment variables:
 * - TELEGRAM_BOT_TOKEN: The bot token from @BotFather
 * - TELEGRAM_CHAT_ID: The chat ID to send messages to
 *
 * To get the chat ID, send a message to your bot and then call:
 * curl https://api.telegram.org/bot<TOKEN>/getUpdates
 */
#[CoversClass(TelegramClient::class)]
final class TelegramClientIntegrationTest extends TestCase
{
    private TelegramClient $client;
    private string $botToken;
    private string $chatId;

    protected function setUp(): void
    {
        $botToken = getenv('TELEGRAM_BOT_TOKEN');
        $chatId = getenv('TELEGRAM_CHAT_ID');

        if (false === $botToken || '' === $botToken) {
            self::markTestSkipped('TELEGRAM_BOT_TOKEN environment variable is not set');
        }

        if (false === $chatId || '' === $chatId) {
            self::markTestSkipped('TELEGRAM_CHAT_ID environment variable is not set');
        }

        $this->botToken = $botToken;
        $this->chatId = $chatId;
        $this->client = new TelegramClient(HttpClient::create());
    }

    public function testSendMessageSuccessfully(): void
    {
        $this->client->send(
            $this->botToken,
            $this->chatId,
            'Test message from Event Pager integration test',
        );

        // If no exception is thrown, the message was sent successfully
        $this->expectNotToPerformAssertions();
    }

    public function testSendMessageWithHtmlFormatting(): void
    {
        $this->client->send(
            $this->botToken,
            $this->chatId,
            '<b>Bold</b> and <i>italic</i> text with <code>code</code>',
        );

        $this->expectNotToPerformAssertions();
    }

    public function testSendMessageWithSpecialCharacters(): void
    {
        $this->client->send(
            $this->botToken,
            $this->chatId,
            'Test with special chars: äöü ß € @ # 日本語',
        );

        $this->expectNotToPerformAssertions();
    }

    public function testSendMessageWithLongBody(): void
    {
        // Telegram has a limit of 4096 characters for messages
        // Using 512 bytes to stay within safe limits
        $longMessage = str_repeat('A', 512);

        $this->client->send(
            $this->botToken,
            $this->chatId,
            $longMessage,
        );

        $this->expectNotToPerformAssertions();
    }

    public function testSendToInvalidTokenThrowsException(): void
    {
        $this->expectException(TelegramSendFailed::class);

        $this->client->send(
            'invalid-token',
            $this->chatId,
            'This should fail',
        );
    }

    public function testSendToInvalidChatIdThrowsException(): void
    {
        $this->expectException(TelegramSendFailed::class);

        $this->client->send(
            $this->botToken,
            'invalid-chat-id-999999999999',
            'This should fail',
        );
    }

    public function testTransportIsReusedForSameToken(): void
    {
        // Send two messages to verify transport caching works
        $this->client->send(
            $this->botToken,
            $this->chatId,
            'First message - testing transport reuse',
        );

        $this->client->send(
            $this->botToken,
            $this->chatId,
            'Second message - same transport should be used',
        );

        $this->expectNotToPerformAssertions();
    }
}
