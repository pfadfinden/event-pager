<?php

declare(strict_types=1);

namespace App\View\Web\PagerManagement;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/pager-management', name: 'web_pager_management_pager')]
#[IsGranted('ROLE_VIEW_PAGER')]
final class PagerManagementController extends AbstractController
{
    public function __construct()
    {
    }

    public function __invoke(): Response
    {
        return $this->render('pager-management/index.html.twig');
    }
}
