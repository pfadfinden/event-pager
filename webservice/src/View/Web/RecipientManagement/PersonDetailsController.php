<?php

declare(strict_types=1);

namespace App\View\Web\RecipientManagement;

use App\Core\Contracts\Bus\CommandBus;
use App\Core\Contracts\Bus\QueryBus;
use App\Core\MessageRecipient\Command\DeleteRecipient;
use App\Core\MessageRecipient\Query\MessageRecipientById;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use function Symfony\Component\Translation\t;

#[Route('/recipients/person/{id}', name: 'web_recipient_management_person_details')]
#[IsGranted('ROLE_VIEW_RECIPIENTS')]
final class PersonDetailsController extends AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly CommandBus $commandBus,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $recipient = $this->queryBus->get(MessageRecipientById::withId($id));
        if (null === $recipient || !$recipient->isPerson()) {
            throw new NotFoundHttpException('Person not found');
        }

        $deleteForm = $this->createFormBuilder()->add('delete', SubmitType::class, ['label' => 'Delete'])->getForm();
        $deleteForm->handleRequest($request);
        if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
            $this->denyAccessUnlessGranted('ROLE_MANAGE_RECIPIENT_INDIVIDUALS');

            try {
                $this->commandBus->do(new DeleteRecipient($recipient->id));
                $this->addFlash('success', t('Person deleted successfully'));

                return $this->redirectToRoute('web_recipient_management_overview');
            } catch (RuntimeException $e) {
                $this->addFlash('error', t('Failed to delete person: {message}', ['message' => $e->getMessage()]));
            }
        }

        return $this->render('recipient-management/person/show.html.twig', [
            'recipient' => $recipient,
            'delete_form' => $deleteForm,
        ]);
    }
}
