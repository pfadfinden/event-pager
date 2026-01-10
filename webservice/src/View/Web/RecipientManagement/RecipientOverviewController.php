<?php

declare(strict_types=1);

namespace App\View\Web\RecipientManagement;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/recipients', name: 'web_recipient_management_overview')]
#[IsGranted('ROLE_VIEW_RECIPIENTS')]
final class RecipientOverviewController extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->render('recipient-management/index.html.twig');
    }
}
