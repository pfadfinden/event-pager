<?php

declare(strict_types=1);

namespace App\View\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/')]
#[IsGranted('ROLE_ACCESS_WEB')]
class HomeController extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->render('home.html.twig');
    }
}
