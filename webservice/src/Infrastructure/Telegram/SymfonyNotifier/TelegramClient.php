<?php

declare(strict_types=1);

namespace App\Infrastructure\Telegram\SymfonyNotifier;

use App\Core\TelegramTransport\Exception\TelegramSendFailed;
use App\Core\TelegramTransport\Port\TelegramClientInterface;
use Symfony\Component\Notifier\Bridge\Telegram\TelegramOptions;
use Symfony\Component\Notifier\Bridge\Telegram\TelegramTransport as SymfonyTelegramTransport;
use Symfony\Component\Notifier\Exception\TransportExceptionInterface;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Telegram client implementation using Symfony Notifier.
 */
final class TelegramClient implements TelegramClientInterface
{
    /**
     * @var array<string, SymfonyTelegramTransport>
     */
    private array $transports = [];

    public function __construct(
        private readonly HttpClientInterface $httpClient,
    ) {
    }

    public function send(
        string $botToken,
        string $chatId,
        string $message,
    ): void {
        $transport = $this->getTransportForToken($botToken);

        $options = new TelegramOptions()
            ->chatId($chatId)
            ->parseMode(TelegramOptions::PARSE_MODE_HTML);

        $chatMessage = new ChatMessage($message);
        $chatMessage->options($options);

        try {
            $transport->send($chatMessage);
        } catch (TransportExceptionInterface $e) {
            throw TelegramSendFailed::withReason($e->getMessage());
        }
    }

    private function getTransportForToken(string $botToken): SymfonyTelegramTransport
    {
        if (!isset($this->transports[$botToken])) {
            $this->transports[$botToken] = new SymfonyTelegramTransport(
                $botToken,
                null,
                $this->httpClient,
            );
        }

        return $this->transports[$botToken];
    }
}
