<?php

declare(strict_types=1);

namespace App\View\Web\PagerManagement;

use App\Core\Contracts\Bus\CommandBus;
use App\Core\Contracts\Bus\QueryBus;
use App\Core\IntelPage\Command\UpdateChannel;
use App\Core\IntelPage\Query\Channel;
use App\View\Web\PagerManagement\Form\ChannelFormType;
use App\View\Web\PagerManagement\Request\ChannelRequest;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Ulid;
use function Symfony\Component\Translation\t;

#[Route('/pager-management/channel/{id}/edit', name: 'web_pager_management_channel_edit')]
#[IsGranted('ROLE_MANAGE_PAGER_CONFIGURATION')]
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
            throw new NotFoundHttpException('Channel not found');
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

            try {
                $this->commandBus->do(new UpdateChannel($channelId->toString(), $channelRequest->name, $channelRequest->capCode, $channelRequest->audible, $channelRequest->vibration));

                $this->addFlash('success', t('Channel updated successfully'));

                return $this->redirectToRoute('web_pager_management_channel_details', ['id' => $channelId->toString()]);
            } catch (RuntimeException $e) {
                $this->addFlash('error', t('Failed to update channel: {message}', ['message' => $e->getMessage()]));
            }
        }

        return $this->render('pager-management/edit-channel.html.twig', [
            'form' => $form,
        ]);
    }
}
