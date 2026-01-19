<?php

declare(strict_types=1);

namespace App\View\Web\Admin\PredefinedMessages;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/predefined-messages', name: 'web_admin_predefined_messages_overview')]
#[IsGranted('ROLE_PREDEFINEDMESSAGES_VIEW')]
final class PredefinedMessagesOverviewController extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->render('admin/predefined-messages/index.html.twig');
    }
}
