<?php

declare(strict_types=1);

namespace App\Infrastructure\Ntfy\SymfonyHttpClient;

use App\Core\NtfyTransport\Exception\NtfySendFailed;
use App\Core\NtfyTransport\Model\NtfyPriority;
use App\Core\NtfyTransport\Port\NtfyClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * HTTP client implementation for ntfy using Symfony HttpClient.
 */
final readonly class NtfyClient implements NtfyClientInterface
{
    public function __construct(
        private HttpClientInterface $httpClient,
    ) {
    }

    public function send(
        string $serverUrl,
        string $topic,
        string $message,
        NtfyPriority $priority,
        ?string $accessToken = null,
    ): void {
        $url = rtrim($serverUrl, '/').'/'.$topic;

        $headers = [
            'X-Priority' => (string) $priority->value,
        ];

        if (null !== $accessToken && '' !== $accessToken) {
            $headers['Authorization'] = 'Bearer '.$accessToken;
        }

        try {
            $response = $this->httpClient->request('POST', $url, [
                'headers' => $headers,
                'body' => $message,
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode >= 400) {
                throw NtfySendFailed::withReason("HTTP {$statusCode}: ".$response->getContent(false));
            }
        } catch (TransportExceptionInterface $e) {
            throw NtfySendFailed::withReason($e->getMessage());
        }
    }
}
