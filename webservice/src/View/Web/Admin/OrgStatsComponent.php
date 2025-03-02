<?php

declare(strict_types=1);

namespace App\View\Web\Admin;

use App\Core\Contracts\Bus\QueryBus;
use App\Core\MessageRecipient\Query\CountOfMessageRecipients;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('OrgStats', template: 'admin/_component/org-stats.html.twig')]
class OrgStatsComponent
{
    use DefaultActionTrait;

    public function __construct(private QueryBus $queryBus)
    {
    }

    public function getGroupCount(): int
    {
        return $this->queryBus->get(CountOfMessageRecipients::onlyGroups());
    }

    public function getRoleCount(): int
    {
        return $this->queryBus->get(CountOfMessageRecipients::onlyRoles());
    }

    public function getPeopleCount(): int
    {
        return $this->queryBus->get(CountOfMessageRecipients::onlyPeople());
    }
}
