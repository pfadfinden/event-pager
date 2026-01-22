<?php

declare(strict_types=1);

namespace App\View\Web\Admin\PredefinedMessages;

use App\Core\Contracts\Bus\CommandBus;
use App\Core\Contracts\Bus\QueryBus;
use App\Core\MessageRecipient\Query\MessageRecipientById;
use App\Core\PredefinedMessages\Command\EditPredefinedMessage;
use App\Core\PredefinedMessages\Query\PredefinedMessageById;
use App\View\Web\Admin\PredefinedMessages\Request\EditPredefinedMessageRequest;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use function Symfony\Component\Translation\t;

#[Route('/admin/predefined-messages/{id}/edit', name: 'web_admin_predefined_messages_edit')]
#[IsGranted('ROLE_PREDEFINEDMESSAGES_EDIT')]
final class EditPredefinedMessageController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly QueryBus $queryBus,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $message = $this->queryBus->get(PredefinedMessageById::withId($id));
        if (null === $message) {
            throw new NotFoundHttpException('Predefined message not found');
        }

        $messageRequest = new EditPredefinedMessageRequest();
        $messageRequest->title = $message->title;
        $messageRequest->messageContent = $message->messageContent;
        $messageRequest->priority = $message->priority;
        $messageRequest->recipientIds = implode(', ', $message->recipientIds);
        $messageRequest->isFavorite = $message->isFavorite;
        $messageRequest->sortOrder = $message->sortOrder;
        $messageRequest->isEnabled = $message->isEnabled;

        $form = $this->createFormBuilder($messageRequest)
            ->add('title', TextType::class, ['label' => t('Title')])
            ->add('messageContent', TextareaType::class, ['label' => t('Message Content')])
            ->add('priority', ChoiceType::class, [
                'label' => t('Priority'),
                'choices' => [
                    '1 - Low' => 1,
                    '2' => 2,
                    '3 - Normal' => 3,
                    '4' => 4,
                    '5 - High' => 5,
                ],
            ])
            ->add('recipientIds', HiddenType::class, [
                'required' => false,
            ])
            ->add('isFavorite', CheckboxType::class, [
                'label' => t('Show in Favorites'),
                'required' => false,
            ])
            ->add('sortOrder', IntegerType::class, [
                'label' => t('Sort Order'),
                'help' => t('Lower numbers appear first'),
            ])
            ->add('isEnabled', CheckboxType::class, [
                'label' => t('Enabled'),
                'required' => false,
            ])
            ->add('save', SubmitType::class, ['label' => t('Save')])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var EditPredefinedMessageRequest $messageRequest */
            $messageRequest = $form->getData();

            try {
                $this->commandBus->do(new EditPredefinedMessage(
                    $message->id,
                    $messageRequest->title,
                    $messageRequest->messageContent,
                    $messageRequest->priority,
                    $messageRequest->getRecipientIdsAsArray(),
                    $messageRequest->isFavorite,
                    $messageRequest->sortOrder,
                    $messageRequest->isEnabled,
                ));

                $this->addFlash('success', t('Predefined message updated successfully'));

                return $this->redirectToRoute('web_admin_predefined_messages_details', ['id' => $message->id]);
            } catch (InvalidArgumentException $e) {
                $this->addFlash('error', t('Failed to update predefined message: {message}', ['message' => $e->getMessage()]));
            }
        }

        // Build initial selected recipients data for the component
        $initialRecipients = [];
        foreach ($message->recipientIds as $recipientId) {
            $recipient = $this->queryBus->get(MessageRecipientById::withId($recipientId));
            if (null !== $recipient) {
                $initialRecipients[] = [
                    'id' => $recipient->id,
                    'name' => $recipient->name,
                    'type' => $recipient->type,
                ];
            }
        }

        return $this->render('admin/predefined-messages/edit.html.twig', [
            'form' => $form,
            'message' => $message,
            'initialRecipients' => $initialRecipients,
        ]);
    }
}
