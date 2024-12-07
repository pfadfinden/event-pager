<?php

declare(strict_types=1);

namespace App\View\Web\Admin;

use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('OrgStats', template: 'admin/_component/org-stats.html.twig')]
class OrgStatsComponent
{
    use DefaultActionTrait;

    public function getGroupCount(): int
    {
        return rand(1, 100);
    }

    public function getRoleCount(): int
    {
        return rand(1, 100);
    }

    public function getPeopleCount(): int
    {
        return rand(1, 500);
    }
}
