<?php

declare(strict_types=1);

namespace App\View\Web\MessageHistory;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/message-history', name: 'web_message_history')]
#[IsGranted('ROLE_VIEW_MESSAGE_HISTORY_SENT_PERSONAL')]
final class MessageHistoryController extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->render('message-history/index.html.twig');
    }
}
