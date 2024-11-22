<?php

declare(strict_types=1);

namespace App\Core\TransportContract\Port;

/**
 * List of active transports.
 */
interface TransportManager
{
    /**
     * @return iterable<Transport>
     */
    public function activeTransports(): iterable;
}
