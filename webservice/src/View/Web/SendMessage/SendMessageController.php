<?php

namespace App\View\Web\SendMessage;

use App\Core\Bus\QueryBus\QueryBus;
use App\Core\SendMessage\Command\SendMessage;
use App\Core\SendMessage\Query\MessagesSendByUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/send')]
class SendMessageController extends AbstractController
{
    // public function __construct(private readonly QueryBus $query) {}

    public function __invoke(Request $request): Response
    {
        $message = new SendMessageRequest();

        $form = $this->createForm(SendMessageFormType::class, $message);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $message = $form->getData();

            // TODO use messenger
            $sendMessageCommand = new SendMessage(
                $message->message,
                'userid',
                $message->priority,
                $message->to,
                // TODO user, timestamp
            );

            return $this->redirectToRoute('app_view_web_sendmessage_sendmessage__invoke');
        }

        // $messageLog = $query->__invoke(new MessagesSendByUser());

        return $this->render('send_message/index.html.twig', [
            'form' => $form,
        ]);
    }
}
