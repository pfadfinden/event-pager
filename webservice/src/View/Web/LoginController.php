<?php

declare(strict_types=1);

namespace App\View\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/login')]
class LoginController extends AbstractController
{
    #[Route('/', name: 'login', methods: ['GET'])]
    public function __invoke(): Response
    {
        return $this->render('login.html.twig');
    }

    #[Route('/submit', name: 'login_submit', methods: ['POST'])]
    public function login(Request $request): Response
    {
        $username = $request->request->get('username');
        $password = $request->request->get('password');

        echo "LoginController.php: login()";
        echo $username;
        echo $password;

        return $this->redirectToRoute('app_view_web_admin_adminhome__invoke');

        // $user = $this->getDoctrine()
        //     ->getRepository(User::class)
        //     ->findOneBy(['username' => $username]);

        // if (!$user) {
        //     return $this->render('login.html.twig', ['error' => 'Invalid username']);
        // }

        // if (!password_verify($password, $user->getPassword())) {
        //     return $this->render('login.html.twig', ['error' => 'Invalid password']);
        // }

        // $this->get('session')->set('user', $user);

        // return $this->redirectToRoute('home');
    }
}
