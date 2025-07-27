<?php

declare(strict_types=1);

namespace App\View\Web\PagerManagement;

use App\Core\Contracts\Bus\CommandBus;
use App\Core\Contracts\Bus\QueryBus;
use App\Core\IntelPage\Command\ActivatePager;
use App\Core\IntelPage\Query\Pager;
use App\Core\MessageRecipient\Query\ListOfMessageRecipients;
use App\Core\MessageRecipient\ReadModel\RecipientListEntry;
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
            throw new NotFoundHttpException();
        }

        $assignableToRecipients = iterator_to_array($this->queryBus->get(ListOfMessageRecipients::onlyPeople()));
        array_push($assignableToRecipients, ...$this->queryBus->get(ListOfMessageRecipients::onlyRoles()));

        $form = $this->createFormBuilder()
           ->add('assign', ChoiceType::class, [
               'choices' => $assignableToRecipients, 'required' => false, 'choice_label' => fn (?RecipientListEntry $recipient) => $recipient?->name, 'choice_value' => fn (?RecipientListEntry $recipient) => $recipient?->id,
           ])
            ->add('changeOnly', SubmitType::class)
            ->add('changeAndActivate', SubmitType::class)
            ->add('changeAndDeactivate', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @phpstan-ignore-next-line method.notFound */
            if ($form->get('changeAndActivate')->isClicked()) {
                $this->commandBus->do(new ActivatePager($pager->id));
                $this->addFlash('success', t('Status & Assignment saved.'));
            }

            // TODO other options

            return $this->redirectToRoute('web_pager_management_pager_details', ['id' => $pagerId->toString()]);
        }

        return $this->render('pager-management/edit-assignment.html.twig', [
            'pager' => $pager,
            'form' => $form,
        ]);
    }
}
