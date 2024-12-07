<?php

declare(strict_types=1);

namespace App\View\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/login')]
class LoginController extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->render('login.html.twig');
    }
}
