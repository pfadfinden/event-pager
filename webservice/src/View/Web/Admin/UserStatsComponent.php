<?php

declare(strict_types=1);

namespace App\View\Web\Admin;

use App\Core\Contracts\Bus\QueryBus;
use App\Core\UserManagement\Query\CountUsers;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('UserStats', template: 'admin/_component/user-stats.html.twig')]
#[IsGranted('ROLE_USERMANAGEMENT_VIEW')]
class UserStatsComponent
{
    use DefaultActionTrait;

    public function __construct(private QueryBus $queryBus)
    {
    }

    public function getUserCount(): int
    {
        return $this->queryBus->get(CountUsers::withoutFilter());
    }
}
