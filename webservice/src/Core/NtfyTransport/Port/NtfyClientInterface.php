<?php

declare(strict_types=1);

namespace App\Core\NtfyTransport\Port;

use App\Core\NtfyTransport\Model\NtfyPriority;

/**
 * Interface for sending messages to an ntfy server.
 */
interface NtfyClientInterface
{
    /**
     * Send a message to an ntfy server.
     *
     * @param string       $serverUrl   The base URL of the ntfy server (e.g., "https://ntfy.sh")
     * @param string       $topic       The topic to publish to
     * @param string       $message     The message body
     * @param NtfyPriority $priority    The message priority
     * @param string|null  $accessToken Optional access token for authenticated servers
     *
     * @throws \App\Core\NtfyTransport\Exception\NtfySendFailed
     */
    public function send(
        string $serverUrl,
        string $topic,
        string $message,
        NtfyPriority $priority,
        ?string $accessToken = null,
    ): void;
}
