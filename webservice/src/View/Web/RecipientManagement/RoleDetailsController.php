<?php

declare(strict_types=1);

namespace App\View\Web\RecipientManagement;

use App\Core\Contracts\Bus\CommandBus;
use App\Core\Contracts\Bus\QueryBus;
use App\Core\MessageRecipient\Command\BindPersonToRole;
use App\Core\MessageRecipient\Command\DeleteRecipient;
use App\Core\MessageRecipient\Query\ListOfMessageRecipients;
use App\Core\MessageRecipient\Query\MessageRecipientById;
use App\View\Web\RecipientManagement\Request\RoleBindingRequest;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use function Symfony\Component\Translation\t;

#[Route('/recipients/role/{id}', name: 'web_recipient_management_role_details')]
#[IsGranted('ROLE_VIEW_RECIPIENTS')]
final class RoleDetailsController extends AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly CommandBus $commandBus,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $recipient = $this->queryBus->get(MessageRecipientById::withId($id));
        if (null === $recipient || !$recipient->isRole()) {
            throw new NotFoundHttpException('Role not found');
        }

        // Delete form
        $deleteForm = $this->createFormBuilder()->add('delete', SubmitType::class, ['label' => 'Delete'])->getForm();
        $deleteForm->handleRequest($request);
        if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
            $this->denyAccessUnlessGranted('ROLE_MANAGE_RECIPIENT_ROLES');

            try {
                $this->commandBus->do(new DeleteRecipient($recipient->id));
                $this->addFlash('success', t('Role deleted successfully'));

                return $this->redirectToRoute('web_recipient_management_overview');
            } catch (RuntimeException $e) {
                $this->addFlash('error', t('Failed to delete role: {message}', ['message' => $e->getMessage()]));
            }
        }

        // Bind person form
        $bindPersonForm = null;
        $unbindPersonForm = null;
        if ($this->isGranted('ROLE_MANAGE_RECIPIENT_ROLES')) {
            if (null === $recipient->assignedPerson) {
                // Show bind form
                $allPersons = $this->queryBus->get(ListOfMessageRecipients::onlyPeople());
                $choices = [];
                foreach ($allPersons as $person) {
                    $choices[$person->name] = $person->id;
                }

                $bindRequest = new RoleBindingRequest();
                $bindPersonForm = $this->createFormBuilder($bindRequest)
                    ->add('personId', ChoiceType::class, [
                        'choices' => $choices,
                        'label' => 'Assign Person',
                        'placeholder' => 'Select person...',
                    ])
                    ->add('save', SubmitType::class, ['label' => 'Bind'])
                    ->getForm();

                $bindPersonForm->handleRequest($request);
                if ($bindPersonForm->isSubmitted() && $bindPersonForm->isValid()) {
                    /** @var RoleBindingRequest $bindRequest */
                    $bindRequest = $bindPersonForm->getData();

                    try {
                        $this->commandBus->do(new BindPersonToRole($recipient->id, $bindRequest->personId));
                        $this->addFlash('success', t('Person bound to role successfully'));

                        return $this->redirectToRoute('web_recipient_management_role_details', ['id' => $recipient->id]);
                    } catch (RuntimeException $e) {
                        $this->addFlash('error', t('Failed to bind person: {message}', ['message' => $e->getMessage()]));
                    }
                }
            } else {
                // Show unbind form
                $unbindPersonForm = $this->createFormBuilder()
                    ->add('unbind', SubmitType::class, ['label' => 'Unbind Person'])
                    ->getForm();

                $unbindPersonForm->handleRequest($request);
                if ($unbindPersonForm->isSubmitted() && $unbindPersonForm->isValid()) {
                    try {
                        $this->commandBus->do(new BindPersonToRole($recipient->id, null));
                        $this->addFlash('success', t('Person unbound from role successfully'));

                        return $this->redirectToRoute('web_recipient_management_role_details', ['id' => $recipient->id]);
                    } catch (RuntimeException $e) {
                        $this->addFlash('error', t('Failed to unbind person: {message}', ['message' => $e->getMessage()]));
                    }
                }
            }
        }

        return $this->render('recipient-management/role/show.html.twig', [
            'recipient' => $recipient,
            'delete_form' => $deleteForm,
            'bind_person_form' => $bindPersonForm,
            'unbind_person_form' => $unbindPersonForm,
        ]);
    }
}
