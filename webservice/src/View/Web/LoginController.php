<?php

declare(strict_types=1);

namespace App\View\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    public function __construct(private readonly AuthenticationUtils $authenticationUtils)
    {
    }
    #[Route('/login/', name: 'app_login')]
    public function __invoke(): Response
    {
        return $this->render('login.html.twig', [
            'error' => $this->authenticationUtils->getLastAuthenticationError(),
        ]);
    }
}
