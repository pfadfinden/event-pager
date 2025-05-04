<?php

declare(strict_types=1);

namespace App\View\Web\PagerManagement;

use App\Core\Contracts\Bus\QueryBus;
use App\Core\IntelPage\Query\AllChannel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/pager-management/channel', name: 'web_pager_management_channel', methods: ['GET'])]
final class ChannelOverviewController extends AbstractController
{
    public function __construct(private readonly QueryBus $queryBus)
    {
    }

    public function __invoke(): Response
    {
        $channel = $this->queryBus->get(AllChannel::withoutFilter());

        return $this->render('pager-management/channel-overview.html.twig', ['channel' => $channel]);
    }
}
