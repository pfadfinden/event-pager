<?php

declare(strict_types=1);

namespace App\View\Web\Admin\PredefinedMessages;

use App\Core\Contracts\Bus\CommandBus;
use App\Core\Contracts\Bus\QueryBus;
use App\Core\PredefinedMessages\Command\DeletePredefinedMessage;
use App\Core\PredefinedMessages\Query\PredefinedMessageById;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use function Symfony\Component\Translation\t;

#[Route('/admin/predefined-messages/{id}', name: 'web_admin_predefined_messages_details')]
#[IsGranted('ROLE_PREDEFINEDMESSAGES_VIEW')]
final class PredefinedMessageDetailsController extends AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly CommandBus $commandBus,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $message = $this->queryBus->get(PredefinedMessageById::withId($id));
        if (null === $message) {
            throw new NotFoundHttpException('Predefined message not found');
        }

        $deleteForm = $this->createFormBuilder()->add('delete', SubmitType::class, ['label' => t('Delete')])->getForm();
        $deleteForm->handleRequest($request);
        if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
            $this->denyAccessUnlessGranted('ROLE_PREDEFINEDMESSAGES_DELETE');

            try {
                $this->commandBus->do(new DeletePredefinedMessage($message->id));
                $this->addFlash('success', t('Predefined message deleted successfully'));

                return $this->redirectToRoute('web_admin_predefined_messages_overview');
            } catch (InvalidArgumentException $e) {
                $this->addFlash('error', t('Failed to delete predefined message: {message}', ['message' => $e->getMessage()]));
            }
        }

        return $this->render('admin/predefined-messages/show.html.twig', [
            'message' => $message,
            'delete_form' => $deleteForm,
        ]);
    }
}
