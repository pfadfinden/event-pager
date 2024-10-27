<?php

namespace App\View\Web\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class AdminHomeController extends AbstractController
{
    public function __invoke(): Response
    {
        $number = random_int(0, 100);

        return $this->render('admin/index.html.twig', [
            'number' => $number,
        ]);
    }
}
