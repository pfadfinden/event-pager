<?php

declare(strict_types=1);

namespace App\View\Web\PagerManagement;

use App\Core\Contracts\Bus\CommandBus;
use App\Core\Contracts\Bus\QueryBus;
use App\Core\IntelPage\Command\ActivatePager;
use App\Core\IntelPage\Command\AssignCarrier;
use App\Core\IntelPage\Command\DeactivatePager;
use App\Core\IntelPage\Query\Pager;
use App\Core\MessageRecipient\Query\ListOfMessageRecipients;
use App\Core\MessageRecipient\ReadModel\RecipientListEntry;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Ulid;
use function Symfony\Component\Translation\t;

#[Route('/pager-management/pager/{id}/assignment', name: 'web_pager_management_pager_assignment')]
#[IsGranted('ROLE_MANAGE_PAGER_STATUS')]
final class PagerAssignmentController extends AbstractController
{
    public function __construct(private readonly QueryBus $queryBus, private readonly CommandBus $commandBus)
    {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $pagerId = Ulid::fromString($id);

        $pager = $this->queryBus->get(Pager::withId($pagerId->toString()));
        if (null === $pager) {
            throw new NotFoundHttpException('Pager not found');
        }

        $assignableToRecipients = iterator_to_array($this->queryBus->get(ListOfMessageRecipients::onlyPeople()));
        array_push($assignableToRecipients, ...$this->queryBus->get(ListOfMessageRecipients::onlyRoles()));

        $currentRecipient = null;
        if (null !== $pager->carriedById) {
            foreach ($assignableToRecipients as $recipient) {
                if ($recipient->id === $pager->carriedById) {
                    $currentRecipient = $recipient;
                    break;
                }
            }
        }

        $form = $this->createFormBuilder()
           ->add('assign', ChoiceType::class, [
               'choices' => $assignableToRecipients,
               'required' => false,
               'choice_label' => fn (?RecipientListEntry $recipient) => $recipient?->name,
               'choice_value' => fn (?RecipientListEntry $recipient) => $recipient?->id,
               'data' => $currentRecipient,
           ])
            ->add('changeOnly', SubmitType::class)
            ->add('changeAndActivate', SubmitType::class)
            ->add('changeAndDeactivate', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                /** @var RecipientListEntry|null $selectedRecipient */
                $selectedRecipient = $form->get('assign')->getData();
                $recipientId = $selectedRecipient?->id;

                $this->commandBus->do(new AssignCarrier($pager->id, $recipientId));

                /** @phpstan-ignore-next-line method.notFound */
                if ($form->get('changeAndActivate')->isClicked()) {
                    $this->commandBus->do(new ActivatePager($pager->id));
                    $this->addFlash('success', t('Status & Assignment saved.'));

                    /** @phpstan-ignore-next-line method.notFound */
                } elseif ($form->get('changeAndDeactivate')->isClicked()) {
                    $this->commandBus->do(new DeactivatePager($pager->id));
                    $this->addFlash('success', t('Status & Assignment saved.'));
                } else {
                    $this->addFlash('success', t('Assignment saved.'));
                }

                return $this->redirectToRoute('web_pager_management_pager_details', ['id' => $pagerId->toString()]);
            } catch (RuntimeException $e) {
                $this->addFlash('error', t('Failed to update assignment: {message}', ['message' => $e->getMessage()]));
            }
        }

        return $this->render('pager-management/edit-assignment.html.twig', [
            'pager' => $pager,
            'form' => $form,
        ]);
    }
}
