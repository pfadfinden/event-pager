<?php

declare(strict_types=1);

namespace App\Core\IntelPage\Port;

use App\Core\IntelPage\Model\CapCode;
use Exception;

interface IntelPageTransmitterInterface
{
    /**
     * @throws Exception
     */
    public function transmit(CapCode $capCode, string $text): void;
}
