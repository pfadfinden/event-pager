<?php

declare(strict_types=1);

namespace App\Core\TransportContract\Port;

use App\Core\TransportContract\Model\SystemTransportConfig;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.transport.factory')]
interface TransportFactory
{
    /**
     * @param class-string $transportClass
     *
     * @return bool this factory can instantiate this transport class
     */
    public function supports(string $transportClass): bool;

    public function withSystemConfiguration(SystemTransportConfig $config): Transport;
}
