<?php

declare(strict_types=1);

namespace App\View\Web\Admin\UserManagement;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/user', name: 'web_admin_user_overview')]
#[IsGranted('ROLE_USERMANAGEMENT_VIEW')]
final class UserOverviewController extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->render('admin/user/index.html.twig');
    }
}
