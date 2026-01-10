<?php

declare(strict_types=1);

namespace App\View\Web\PagerManagement;

use App\Core\Contracts\Bus\CommandBus;
use App\Core\Contracts\Bus\QueryBus;
use App\Core\IntelPage\Command\RemovePager;
use App\Core\IntelPage\Query\CapAssignments;
use App\Core\IntelPage\Query\Pager;
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

#[Route('/pager-management/pager/{id}', name: 'web_pager_management_pager_details')]
#[IsGranted('ROLE_VIEW_PAGER')]
class PagerDetailsController extends AbstractController
{
    public function __construct(private readonly QueryBus $queryBus, private readonly CommandBus $commandBus)
    {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $pagerId = Ulid::fromString($id);

        $pager = $this->queryBus->get(Pager::withId($pagerId->toString()));
        if (null === $pager) {
            throw new NotFoundHttpException('Pager not found');
        }

        $deleteForm = $this->createFormBuilder()->add('delete', SubmitType::class)->getForm();
        $deleteForm->handleRequest($request);
        if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
            $this->denyAccessUnlessGranted('ROLE_MANAGE_PAGER_CONFIGURATION', null, 'User tried to access a page without having ROLE_MANAGE_PAGER_CONFIGURATION');

            try {
                $this->commandBus->do(new RemovePager($pager->id));
                $this->addFlash('success', t('Pager deleted.'));

                return $this->redirectToRoute('web_pager_management_pager');
            } catch (RuntimeException $e) {
                $this->addFlash('error', t('Failed to delete pager: {message}', ['message' => $e->getMessage()]));
            }
        }

        $capAssignments = $this->queryBus->get(CapAssignments::forPagerWithId($pagerId->toString()));

        return $this->render('pager-management/show-pager.html.twig', [
            'pager' => $pager,
            'capAssignments' => $capAssignments,
            'delete_form' => $deleteForm,
        ]);
    }
}
