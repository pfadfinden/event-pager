<?php

declare(strict_types=1);

namespace App\View\Web\RecipientManagement;

use App\Core\Contracts\Bus\CommandBus;
use App\Core\Contracts\Bus\QueryBus;
use App\Core\MessageRecipient\Command\BindRecipientToGroup;
use App\Core\MessageRecipient\Command\DeleteRecipient;
use App\Core\MessageRecipient\Query\ListOfMessageRecipients;
use App\Core\MessageRecipient\Query\MessageRecipientById;
use App\View\Web\RecipientManagement\Request\GroupMemberRequest;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use function in_array;
use function Symfony\Component\Translation\t;

#[Route('/recipients/group/{id}', name: 'web_recipient_management_group_details')]
#[IsGranted('ROLE_VIEW_RECIPIENTS')]
final class GroupDetailsController extends AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly CommandBus $commandBus,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $recipient = $this->queryBus->get(MessageRecipientById::withId($id));
        if (null === $recipient || !$recipient->isGroup()) {
            throw new NotFoundHttpException('Group not found');
        }

        // Delete form
        $deleteForm = $this->createFormBuilder()->add('delete', SubmitType::class, ['label' => 'Delete'])->getForm();
        $deleteForm->handleRequest($request);
        if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
            $this->denyAccessUnlessGranted('ROLE_MANAGE_RECIPIENT_GROUPS');

            try {
                $this->commandBus->do(new DeleteRecipient($recipient->id));
                $this->addFlash('success', t('Group deleted successfully'));

                return $this->redirectToRoute('web_recipient_management_overview');
            } catch (RuntimeException $e) {
                $this->addFlash('error', t('Failed to delete group: {message}', ['message' => $e->getMessage()]));
            }
        }

        // Add member form
        $addMemberForm = null;
        if ($this->isGranted('ROLE_MANAGE_RECIPIENT_GROUPS')) {
            $allRecipients = $this->queryBus->get(ListOfMessageRecipients::all());
            $choices = [];
            $existingMemberIds = array_map(fn ($m) => $m->id, $recipient->members);

            foreach ($allRecipients as $r) {
                // Don't show self or existing members
                if ($r->id !== $recipient->id && !in_array($r->id, $existingMemberIds, true)) {
                    $choices[$r->name.' ('.$r->type.')'] = $r->id;
                }
            }

            $memberRequest = new GroupMemberRequest();
            $addMemberForm = $this->createFormBuilder($memberRequest)
                ->add('recipientId', ChoiceType::class, [
                    'choices' => $choices,
                    'label' => 'Recipient',
                    'placeholder' => 'Select recipient...',
                ])
                ->add('save', SubmitType::class, ['label' => 'Add'])
                ->getForm();

            $addMemberForm->handleRequest($request);
            if ($addMemberForm->isSubmitted() && $addMemberForm->isValid()) {
                /** @var GroupMemberRequest $memberRequest */
                $memberRequest = $addMemberForm->getData();

                try {
                    $this->commandBus->do(new BindRecipientToGroup($recipient->id, $memberRequest->recipientId));
                    $this->addFlash('success', t('Member added successfully'));

                    return $this->redirectToRoute('web_recipient_management_group_details', ['id' => $recipient->id]);
                } catch (RuntimeException $e) {
                    $this->addFlash('error', t('Failed to add member: {message}', ['message' => $e->getMessage()]));
                }
            }
        }

        return $this->render('recipient-management/group/show.html.twig', [
            'recipient' => $recipient,
            'delete_form' => $deleteForm,
            'add_member_form' => $addMemberForm,
        ]);
    }
}
