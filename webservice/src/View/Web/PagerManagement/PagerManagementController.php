<?php

declare(strict_types=1);

namespace App\View\Web\PagerManagement;

use App\Core\Contracts\Bus\QueryBus;
use App\Core\IntelPage\Query\AllPager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/pager-management', name: 'web_pager_management_pager')]
final class PagerManagementController extends AbstractController
{
    public function __construct(private readonly QueryBus $queryBus)
    {
    }

    public function __invoke(): Response
    {
        $pager = $this->queryBus->get(AllPager::withoutFilter());

        return $this->render('pager-management/index.html.twig', ['pager' => $pager]);
    }
}
