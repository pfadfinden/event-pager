<?php

declare(strict_types=1);

namespace App\View\Web\PagerManagement;

use App\Core\Contracts\Bus\CommandBus;
use App\Core\IntelPage\Command\AddPager;
use App\View\Web\PagerManagement\Request\AddPagerRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Ulid;

#[Route('/pager-management/add-pager', name: 'web_pager_management_pager_add')]
final class AddPagerController extends AbstractController
{
    public function __construct(private readonly CommandBus $commandBus)
    {
    }

    public function __invoke(Request $request): Response
    {
        $addPagerRequest = new AddPagerRequest();
        $form = $this->createFormBuilder($addPagerRequest)
            ->add('number', NumberType::class)
            ->add('label', TextType::class)
            ->add('save', SubmitType::class, ['label' => 'Add Pager'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var AddPagerRequest $addPagerRequest */
            $addPagerRequest = $form->getData();

            $id = Ulid::generate();
            $this->commandBus->do(new AddPager($id, $addPagerRequest->label, $addPagerRequest->number));

            return $this->redirectToRoute('web_pager_management_pager_details', ['id' => $id]);
        }

        return $this->render('pager-management/add-pager.html.twig', [
            'form' => $form,
        ]);
    }
}
