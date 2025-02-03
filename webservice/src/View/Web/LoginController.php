<?php

declare(strict_types=1);

namespace App\View\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route('/login')]
class LoginController extends AbstractController
{
    #[Route('/', name: 'app_login')]
    public function __invoke(AuthenticationUtils $authenticationUtils): Response
    {
        return $this->render('login.html.twig', [
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }
}
