<?php

declare(strict_types=1);

namespace App\View\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/pager-management')]
class PagerManagementController extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->render('pager-management/index.html.twig');
    }
}
