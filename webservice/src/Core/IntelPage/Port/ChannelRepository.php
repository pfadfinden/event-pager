<?php

declare(strict_types=1);

namespace App\Core\IntelPage\Port;

use App\Core\IntelPage\Model\Channel;
use Symfony\Component\Uid\Ulid;

interface ChannelRepository
{
    public function getById(Ulid $id): ?Channel;

    public function persist(Channel $channel): void;

    public function remove(Channel $channel): void;
}
