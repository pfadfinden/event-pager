<?php

declare(strict_types=1);

namespace App\View\Web\PagerManagement;

use App\Core\Contracts\Bus\CommandBus;
use App\Core\Contracts\Bus\QueryBus;
use App\Core\IntelPage\Command\AddChannel;
use App\Core\IntelPage\Command\UpdateChannel;
use App\Core\IntelPage\Query\Channel;
use App\View\Web\PagerManagement\Form\ChannelFormType;
use App\View\Web\PagerManagement\Request\ChannelRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Ulid;

#[Route('/pager-management/channel/{id}/edit', name: 'web_pager_management_channel_edit')]
final class EditChannelController extends AbstractController
{
    public function __construct(private readonly CommandBus $commandBus, private readonly QueryBus $queryBus)
    {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $channelId = Ulid::fromString($id);

        $channel = $this->queryBus->get(Channel::withId($channelId->toString()));
        if (null === $channel) {
            throw new NotFoundHttpException();
        }

        $channelRequest = new ChannelRequest();
        $channelRequest->name = $channel->name;
        $channelRequest->capCode = $channel->capCode;
        $channelRequest->audible = $channel->audible;
        $channelRequest->vibration = $channel->vibration;

        $form = $this->createForm(ChannelFormType::class, $channelRequest);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ChannelRequest $channelRequest */
            $channelRequest = $form->getData();

            $this->commandBus->do(new UpdateChannel($channelId->toString(), $channelRequest->name, $channelRequest->capCode, $channelRequest->audible, $channelRequest->vibration));

            return $this->redirectToRoute('web_pager_management_channel_details', ['id' => $channelId->toString()]);
        }

        return $this->render('pager-management/edit-channel.html.twig', [
            'form' => $form,
        ]);
    }
}
