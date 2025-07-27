<?php

declare(strict_types=1);

namespace App\View\Web\Admin;

use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use const PHP_VERSION;

#[AsLiveComponent('SystemInfo', template: 'admin/_component/system_info.html.twig')]
#[IsGranted('ROLE_ADMIN')]
class SystemInfoComponent
{
    use DefaultActionTrait;

    public string $phpVersion = PHP_VERSION;

    public function getTime(): int
    {
        return time();
    }

    public function getTimeZone(): string
    {
        return date_default_timezone_get();
    }
}
