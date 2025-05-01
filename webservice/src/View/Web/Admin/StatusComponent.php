<?php

declare(strict_types=1);

namespace App\View\Web\Admin;

use App\Core\Contracts\Bus\QueryBus;
use App\Core\SendMessage\Query\CountOfSendMessages;
use Brick\DateTime\Duration;
use Brick\DateTime\Instant;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('Status', template: 'admin/_component/status.html.twig')]
class StatusComponent
{
    use DefaultActionTrait;

    public function __construct(private readonly QueryBus $queryBus)
    {
    }

    public function getLast24Count(): int
    {
        return $this->queryBus->get(CountOfSendMessages::allSendSince(Instant::now()->minus(Duration::ofHours(24))));
    }

    public function getTotalCount(): int
    {
        return $this->queryBus->get(CountOfSendMessages::all());
    }

    public function getErrorLast24Count(): int
    {
        return rand(1, 500);
    }
}
