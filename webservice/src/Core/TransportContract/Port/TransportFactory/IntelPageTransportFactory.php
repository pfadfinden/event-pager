<?php

declare(strict_types=1);

namespace App\Core\TransportContract\Port\TransportFactory;

use App\Core\IntelPage\Application\IntelPageTransport;
use App\Core\TransportContract\Model\SystemTransportConfig;

interface IntelPageTransportFactory
{
    public function withSystemConfiguration(SystemTransportConfig $config): IntelPageTransport;
}
