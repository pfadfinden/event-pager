<?php

declare(strict_types=1);

namespace App\Core\IntelPage\Port;

use App\Core\IntelPage\Model\PagerMessage;
use Exception;

/**
 * Service to send a pager message encapsulating the business domain around it.
 */
interface SendPagerMessageServiceInterface
{
    public function nextMessageToSend(string $transportKey): ?PagerMessage;

    /**
     * Transmits a message and acts based on the success/failure state.
     *
     * Will mark the message and raise events according to the message state.
     * If no exception is thrown, the message was sent successfully.
     *
     * @throws Exception
     */
    public function send(PagerMessage $message): void;
}
