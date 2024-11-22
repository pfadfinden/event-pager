<?php

declare(strict_types=1);

namespace App\Core\TransportContract\Model;

use App\Core\TransportContract\Port\Transport;
use Symfony\Component\Uid\Ulid;

interface MessageRecipient
{
    public function getId(): Ulid;

    // @phpstan-ignore-next-line missingType.iterableValue (JSON compatible array)
    public function getTransportConfigurationFor(Transport $transport): ?array;
}
