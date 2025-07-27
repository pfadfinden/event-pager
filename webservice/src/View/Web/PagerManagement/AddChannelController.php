<?php

declare(strict_types=1);

namespace App\View\Web\PagerManagement;

use App\Core\Contracts\Bus\CommandBus;
use App\Core\IntelPage\Command\AddChannel;
use App\View\Web\PagerManagement\Form\ChannelFormType;
use App\View\Web\PagerManagement\Request\ChannelRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Ulid;

#[Route('/pager-management/add-channel', name: 'web_pager_management_channel_add')]
#[IsGranted('ROLE_MANAGE_PAGER_CONFIGURATION')]
final class AddChannelController extends AbstractController
{
    public function __construct(private readonly CommandBus $commandBus)
    {
    }

    public function __invoke(Request $request): Response
    {
        $channelRequest = new ChannelRequest();
        $form = $this->createForm(ChannelFormType::class, $channelRequest);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ChannelRequest $channelRequest */
            $channelRequest = $form->getData();

            $id = Ulid::generate();
            $this->commandBus->do(new AddChannel($id, $channelRequest->name, $channelRequest->capCode, $channelRequest->audible, $channelRequest->vibration));

            return $this->redirectToRoute('web_pager_management_channel_details', ['id' => $id]);
        }

        return $this->render('pager-management/add-channel.html.twig', [
            'form' => $form,
        ]);
    }
}
