<?php

declare(strict_types=1);

namespace App\View\Web\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ACCESS_WEB_ADMIN')]
class AdminHomeController extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->render('admin/index.html.twig');
    }
}
