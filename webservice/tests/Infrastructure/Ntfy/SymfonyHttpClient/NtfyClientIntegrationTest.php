<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Ntfy\SymfonyHttpClient;

use App\Core\NtfyTransport\Exception\NtfySendFailed;
use App\Core\NtfyTransport\Model\NtfyPriority;
use App\Infrastructure\Ntfy\SymfonyHttpClient\NtfyClient;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;

/**
 * Integration test for NtfyClient against a real ntfy server.
 *
 * Requires the ntfy container to be running (via docker compose).
 * The ntfy server is available at http://ntfy:80 from within the PHP container.
 */
#[CoversClass(NtfyClient::class)]
#[Group('integration')]
final class NtfyClientIntegrationTest extends TestCase
{
    private const string NTFY_SERVER_URL = 'http://ntfy:80';
    private const string TEST_TOPIC = 'event-pager-test';

    private NtfyClient $client;

    protected function setUp(): void
    {
        $this->client = new NtfyClient(HttpClient::create());
    }

    public function testSendMessageSuccessfully(): void
    {
        $this->client->send(
            self::NTFY_SERVER_URL,
            self::TEST_TOPIC,
            'Test message from Event Pager integration test',
            NtfyPriority::DEFAULT,
        );

        // If no exception is thrown, the message was sent successfully
        $this->expectNotToPerformAssertions();
    }

    public function testSendMessageWithAllPriorities(): void
    {
        foreach (NtfyPriority::cases() as $priority) {
            $this->client->send(
                self::NTFY_SERVER_URL,
                self::TEST_TOPIC,
                "Test message with priority {$priority->name}",
                $priority,
            );
        }

        // If no exception is thrown, all messages were sent successfully
        $this->expectNotToPerformAssertions();
    }

    public function testSendMessageWithEmptyBody(): void
    {
        $this->client->send(
            self::NTFY_SERVER_URL,
            self::TEST_TOPIC,
            '',
            NtfyPriority::DEFAULT,
        );

        $this->expectNotToPerformAssertions();
    }

    public function testSendMessageWithLongBody(): void
    {
        // ntfy has a limit of ~4KB for messages without being treated as attachments
        // Using 512 bytes to stay within safe limits
        $longMessage = str_repeat('A', 512);

        $this->client->send(
            self::NTFY_SERVER_URL,
            self::TEST_TOPIC,
            $longMessage,
            NtfyPriority::HIGH,
        );

        $this->expectNotToPerformAssertions();
    }

    public function testSendMessageWithSpecialCharacters(): void
    {
        $this->client->send(
            self::NTFY_SERVER_URL,
            self::TEST_TOPIC,
            'Test with special chars: äöü ß € @ # 日本語 🚨',
            NtfyPriority::DEFAULT,
        );

        $this->expectNotToPerformAssertions();
    }

    public function testSendToInvalidServerThrowsException(): void
    {
        $this->expectException(NtfySendFailed::class);

        $this->client->send(
            'http://invalid-server-that-does-not-exist.local:12345',
            self::TEST_TOPIC,
            'This should fail',
            NtfyPriority::DEFAULT,
        );
    }
}
