<?php

declare(strict_types=1);

namespace App\View\Web\Admin\PredefinedMessages;

use App\Core\Contracts\Bus\CommandBus;
use App\Core\PredefinedMessages\Command\AddPredefinedMessage;
use App\View\Web\Admin\PredefinedMessages\Request\AddPredefinedMessageRequest;
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
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use function Symfony\Component\Translation\t;

#[Route('/admin/predefined-messages/add', name: 'web_admin_predefined_messages_add')]
#[IsGranted('ROLE_PREDEFINEDMESSAGES_ADD')]
final class AddPredefinedMessageController extends AbstractController
{
    public function __construct(private readonly CommandBus $commandBus)
    {
    }

    public function __invoke(Request $request): Response
    {
        $messageRequest = new AddPredefinedMessageRequest();
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
            ->add('save', SubmitType::class, ['label' => t('Add Predefined Message')])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var AddPredefinedMessageRequest $messageRequest */
            $messageRequest = $form->getData();

            try {
                $this->commandBus->do(new AddPredefinedMessage(
                    $messageRequest->title,
                    $messageRequest->messageContent,
                    $messageRequest->priority,
                    $messageRequest->getRecipientIdsAsArray(),
                    $messageRequest->isFavorite,
                    $messageRequest->sortOrder,
                    $messageRequest->isEnabled,
                ));

                $this->addFlash('success', t('Predefined message created successfully'));

                return $this->redirectToRoute('web_admin_predefined_messages_overview');
            } catch (InvalidArgumentException $e) {
                $this->addFlash('error', t('Failed to create predefined message: {message}', ['message' => $e->getMessage()]));
            }
        }

        return $this->render('admin/predefined-messages/add.html.twig', [
            'form' => $form,
        ]);
    }
}
