<?php

declare(strict_types=1);

namespace App\View\Web\PagerManagement;

use App\Core\Contracts\Bus\CommandBus;
use App\Core\Contracts\Bus\QueryBus;
use App\Core\IntelPage\Command\UpdatePager;
use App\Core\IntelPage\Query\Pager;
use App\View\Web\PagerManagement\Request\PagerRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Ulid;

#[Route('/pager-management/pager/{id}/edit', name: 'web_pager_management_pager_edit')]
#[IsGranted('ROLE_MANAGE_PAGER_CONFIGURATION')]
final class EditPagerController extends AbstractController
{
    public function __construct(private readonly CommandBus $commandBus, private readonly QueryBus $queryBus)
    {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $pagerId = Ulid::fromString($id);

        $pager = $this->queryBus->get(Pager::withId($pagerId->toString()));
        if (null === $pager) {
            throw new NotFoundHttpException();
        }

        $pagerRequest = new PagerRequest();
        $pagerRequest->label = $pager->label;
        $pagerRequest->number = $pager->number;
        $pagerRequest->comment = '';

        $form = $this->createFormBuilder($pagerRequest)
            ->add('number', NumberType::class)
            ->add('label', TextType::class)
            ->add('comment', TextType::class)
            ->add('save', SubmitType::class, ['label' => 'Save'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var PagerRequest $pagerRequest */
            $pagerRequest = $form->getData();

            $this->commandBus->do(new UpdatePager($pagerId->toString(), $pagerRequest->label, $pagerRequest->number, $pager->carriedById));

            // TODO sort editing cabailities correctly
            return $this->redirectToRoute('web_pager_management_pager_details', ['id' => $pagerId->toString()]);
        }

        return $this->render('pager-management/edit-pager.html.twig', [
            'form' => $form,
        ]);
    }
}
