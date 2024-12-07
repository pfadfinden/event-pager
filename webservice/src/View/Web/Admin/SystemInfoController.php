<?php

declare(strict_types=1);

namespace App\View\Web\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/system-info')]
class SystemInfoController extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->render('admin/system_info.html.twig');
    }
}
