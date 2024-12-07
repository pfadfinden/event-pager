<?php

declare(strict_types=1);

namespace App\View\Web\Admin;

use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('Status', template: 'admin/_component/status.html.twig')]
class StatusComponent
{
    use DefaultActionTrait;

    public function getLast24Count(): int
    {
        return rand(1, 100);
    }

    public function getTotalCount(): int
    {
        return rand(1, 100);
    }

    public function getErrorLast24Count(): int
    {
        return rand(1, 500);
    }
}
