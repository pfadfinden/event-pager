<?php

declare(strict_types=1);

namespace App\View\Web\PagerManagement;

use App\Core\Contracts\Bus\CommandBus;
use App\Core\Contracts\Bus\QueryBus;
use App\Core\IntelPage\Command\RemoveChannel;
use App\Core\IntelPage\Query\AllPagerWithChannel;
use App\Core\IntelPage\Query\Channel;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Ulid;
use function Symfony\Component\Translation\t;

#[Route('/pager-management/channel/{id}', name: 'web_pager_management_channel_details')]
#[IsGranted('ROLE_VIEW_PAGER')]
class ChannelDetailsController extends AbstractController
{
    public function __construct(private readonly QueryBus $queryBus, private readonly CommandBus $commandBus)
    {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $channelId = Ulid::fromString($id);

        $channel = $this->queryBus->get(Channel::withId($channelId->toString()));
        if (null === $channel) {
            throw new NotFoundHttpException('Channel not found');
        }

        $deleteForm = $this->createFormBuilder()->add('delete', SubmitType::class)->getForm();
        $deleteForm->handleRequest($request);
        if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
            $this->denyAccessUnlessGranted('ROLE_MANAGE_PAGER_CONFIGURATION', null, 'User tried to access a page without having ROLE_MANAGE_PAGER_CONFIGURATION');

            try {
                $this->commandBus->do(new RemoveChannel($channel->id));
                $this->addFlash('success', t('Channel deleted successfully'));

                return $this->redirectToRoute('web_pager_management_channel');
            } catch (RuntimeException $e) {
                $this->addFlash('error', t('Failed to delete channel: {message}', ['message' => $e->getMessage()]));
            }
        }

        $pager = $this->queryBus->get(AllPagerWithChannel::withId($channelId->toString()));

        return $this->render('pager-management/show-channel.html.twig', [
            'channel' => $channel,
            'pager' => $pager,
            'delete_form' => $deleteForm,
        ]);
    }
}
