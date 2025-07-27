<?php

declare(strict_types=1);

namespace App\View\Web\PagerManagement;

use App\Core\Contracts\Bus\QueryBus;
use App\Core\IntelPage\Query\AllPager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/pager-management', name: 'web_pager_management_pager')]
#[IsGranted('ROLE_VIEW_PAGER')]
final class PagerManagementController extends AbstractController
{
    public function __construct(private readonly QueryBus $queryBus)
    {
    }

    public function __invoke(): Response
    {
        $pager = $this->queryBus->get(AllPager::withoutFilter());

        // $this->denyAccessUnlessGranted('ROLE_MANAGE_PAGER_CONFIGURATION', null, 'User tried to access a page without having ROLE_MANAGE_PAGER_CONFIGURATION');

        return $this->render('pager-management/index.html.twig', ['pager' => $pager]);
    }
}
