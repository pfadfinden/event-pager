<?php

declare(strict_types=1);

namespace App\Core\IntelPage\Port;

use Exception;

interface IntelPageSenderInterface
{
    /**
     * @throws Exception
     */
    public function transmit(int $capCode, string $text): void;
}
