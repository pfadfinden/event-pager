<?php

namespace App\View\Web\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/system-info')]
class SystemInfoController extends AbstractController
{
    public function __invoke(): Response
    {
        $time = time();
        $timezone = date_default_timezone_get();
        $phpVersion = \PHP_VERSION;

        return $this->render('admin/system_info.html.twig', [
            'time' => $time,
            'timezone' => $timezone,
            'phpVersion' => $phpVersion,
        ]);
    }
}
