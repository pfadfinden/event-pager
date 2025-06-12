<?php

declare(strict_types=1);

namespace App\View\Web\PagerManagement;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/pager-management/pager/{id}', name: 'web_pager_management_pager_details')]
class PagerDetailsController extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->render('pager-management/show-pager.html.twig');
    }
}
