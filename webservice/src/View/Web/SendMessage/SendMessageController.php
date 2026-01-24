<?php

declare(strict_types=1);

namespace App\View\Web\SendMessage;

use App\Core\Contracts\Bus\CommandBus;
use App\Core\Contracts\Bus\QueryBus;
use App\Core\PredefinedMessages\Query\PredefinedMessageById;
use App\Core\SendMessage\Command\SendMessage;
use App\View\Web\SendMessage\Form\GroupsOnlyRecipientsChoiceLoader;
use App\View\Web\SendMessage\Form\SendMessageFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/send')]
final class SendMessageController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly QueryBus $queryBus,
        private readonly GroupsOnlyRecipientsChoiceLoader $recipientsChoiceLoader,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $message = new SendMessageRequest();

        // Handle predefined message parameter
        $predefinedId = $request->query->getString('predefined');
        if ('' !== $predefinedId) {
            $predefined = $this->queryBus->get(PredefinedMessageById::withId($predefinedId));
            if (null !== $predefined) {
                $message->message = $predefined->messageContent;
                $message->priority = $predefined->priority;
                foreach ($predefined->recipientIds as $recipientId) {
                    $recipient = new SendMessageRecipientRequest();
                    $recipient->id = $recipientId;
                    $message->to[] = $recipient;
                }
            }
        }
        $form = $this->createForm(SendMessageFormType::class, $message, ['choice_loader' => $this->recipientsChoiceLoader]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var SendMessageRequest $message */
            $message = $form->getData();

            $sendMessage = new SendMessage(
                $message->message,
                '01JNAY9HWQTEX1T45VBM2HG1XJ', // TODO user
                $message->priority,
                $message->toIds(),
            );
            $this->commandBus->do($sendMessage);

            // Redirect to this route, but with empty form
            return $this->redirectToRoute(self::class);
        }

        return $this->render('send_message/index.html.twig', [
            'form' => $form,
        ]);
    }
}
