<?php

declare(strict_types=1);

namespace App\View\Web\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/system-info')]
#[IsGranted('ROLE_ADMIN')]
class SystemInfoController extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->render('admin/system_info.html.twig');
    }
}
