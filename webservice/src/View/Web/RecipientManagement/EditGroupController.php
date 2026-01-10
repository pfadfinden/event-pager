<?php

declare(strict_types=1);

namespace App\View\Web\RecipientManagement;

use App\Core\Contracts\Bus\CommandBus;
use App\Core\Contracts\Bus\QueryBus;
use App\Core\MessageRecipient\Command\ReplaceName;
use App\Core\MessageRecipient\Query\MessageRecipientById;
use App\View\Web\RecipientManagement\Request\RecipientNameRequest;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use function Symfony\Component\Translation\t;

#[Route('/recipients/group/{id}/edit', name: 'web_recipient_management_group_edit')]
#[IsGranted('ROLE_MANAGE_RECIPIENT_GROUPS')]
final class EditGroupController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly QueryBus $queryBus,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $recipient = $this->queryBus->get(MessageRecipientById::withId($id));
        if (null === $recipient || !$recipient->isGroup()) {
            throw new NotFoundHttpException('Group not found');
        }

        $recipientRequest = new RecipientNameRequest();
        $recipientRequest->name = $recipient->name;

        $form = $this->createFormBuilder($recipientRequest)
            ->add('name', TextType::class, ['label' => 'Name'])
            ->add('save', SubmitType::class, ['label' => 'Save'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var RecipientNameRequest $recipientRequest */
            $recipientRequest = $form->getData();

            try {
                $this->commandBus->do(new ReplaceName($recipient->id, $recipientRequest->name));

                $this->addFlash('success', t('Group updated successfully'));

                return $this->redirectToRoute('web_recipient_management_group_details', ['id' => $recipient->id]);
            } catch (RuntimeException $e) {
                $this->addFlash('error', t('Failed to update group: {message}', ['message' => $e->getMessage()]));
            }
        }

        return $this->render('recipient-management/group/edit.html.twig', [
            'form' => $form,
            'recipient' => $recipient,
        ]);
    }
}
