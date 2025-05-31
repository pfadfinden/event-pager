<?php

declare(strict_types=1);

namespace App\Core\IntelPage\Query;

use App\Core\Contracts\Bus\Query;
use App\Core\IntelPage\Model\CapCode;

/**
 * @implements Query<CapCode|null>
 */
readonly class ChannelCapCodeById implements Query
{
    public function __construct(public string $channelId)
    {
    }
}
