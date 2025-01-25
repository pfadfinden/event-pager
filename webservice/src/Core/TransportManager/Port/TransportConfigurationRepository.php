<?php

declare(strict_types=1);

namespace App\Core\TransportManager\Port;

use App\Core\TransportManager\Model\TransportConfiguration;

interface TransportConfigurationRepository
{
    public function getByKey(string $key): ?TransportConfiguration;

    public function persist(TransportConfiguration $transportConfiguration): void;

    public function removeByKey(string $key): void;
}
