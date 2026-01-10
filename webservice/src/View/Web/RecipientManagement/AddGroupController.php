<?php

declare(strict_types=1);

namespace App\View\Web\RecipientManagement;

use App\Core\Contracts\Bus\CommandBus;
use App\Core\MessageRecipient\Command\CreateRecipient;
use App\View\Web\RecipientManagement\Request\RecipientNameRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Ulid;
use function Symfony\Component\Translation\t;

#[Route('/recipients/group/add', name: 'web_recipient_management_group_add')]
#[IsGranted('ROLE_MANAGE_RECIPIENT_GROUPS')]
final class AddGroupController extends AbstractController
{
    public function __construct(private readonly CommandBus $commandBus)
    {
    }

    public function __invoke(Request $request): Response
    {
        $recipientRequest = new RecipientNameRequest();
        $form = $this->createFormBuilder($recipientRequest)
            ->add('name', TextType::class, ['label' => 'Name'])
            ->add('save', SubmitType::class, ['label' => 'Add Group'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var RecipientNameRequest $recipientRequest */
            $recipientRequest = $form->getData();

            $id = Ulid::generate();
            $this->commandBus->do(new CreateRecipient($id, 'group', $recipientRequest->name));

            $this->addFlash('success', t('Group created successfully'));

            return $this->redirectToRoute('web_recipient_management_group_details', ['id' => $id]);
        }

        return $this->render('recipient-management/group/add.html.twig', [
            'form' => $form,
        ]);
    }
}
