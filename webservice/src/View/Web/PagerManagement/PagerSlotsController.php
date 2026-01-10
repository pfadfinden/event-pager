<?php

declare(strict_types=1);

namespace App\View\Web\PagerManagement;

use App\Core\Contracts\Bus\CommandBus;
use App\Core\Contracts\Bus\QueryBus;
use App\Core\IntelPage\Command\AssignChannel;
use App\Core\IntelPage\Command\AssignIndividualCapCode;
use App\Core\IntelPage\Command\ClearSlot;
use App\Core\IntelPage\Query\AllChannel;
use App\Core\IntelPage\Query\Pager;
use App\View\Web\PagerManagement\Form\SlotAssignmentFormType;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Ulid;
use function Symfony\Component\Translation\t;

#[Route('/pager-management/pager/{id}/slots/{index}', name: 'web_pager_management_pager_slot')]
#[IsGranted('ROLE_MANAGE_PAGER_CONFIGURATION')]
class PagerSlotsController extends AbstractController
{
    public function __construct(private readonly QueryBus $queryBus, private readonly CommandBus $commandBus)
    {
    }

    public function __invoke(Request $request, string $id, int $index): Response
    {
        $pagerId = Ulid::fromString($id);

        $pager = $this->queryBus->get(Pager::withId($pagerId->toString()));
        if (null === $pager) {
            throw new NotFoundHttpException('Pager not found');
        }

        $channels = $this->queryBus->get(AllChannel::withoutFilter());

        $form = $this->createForm(SlotAssignmentFormType::class, null, [
            'channels' => $channels,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                switch ($form->get('assignment_type')->getData()) {
                    case 0:
                        $this->commandBus->do(new ClearSlot($pager->id, $index));
                        $this->addFlash('success', t('Slot cleared.'));
                        break;
                    case 1:
                        $this->commandBus->do(new AssignIndividualCapCode(
                            $pager->id,
                            $index,
                            /** @phpstan-ignore-next-line cast.int too strict, no workaround */
                            (int) $form->get('cap_code')->getData(),
                            (bool) $form->get('audible')->getData(),
                            (bool) $form->get('vibration')->getData(),
                        ));
                        $this->addFlash('success', t('Individual Cap Code assigned.'));
                        break;
                    case 2:
                        $this->commandBus->do(new AssignChannel(
                            /** @phpstan-ignore-next-line cast.string too strict, no workaround */
                            $pager->id, $index, (string) $form->get('channel_id')->getViewData()
                        ));
                        $this->addFlash('success', t('Channel assigned.'));
                        break;
                }

                return $this->redirectToRoute('web_pager_management_pager_details', ['id' => $pagerId->toString()]);
            } catch (RuntimeException $e) {
                $this->addFlash('error', t('Failed to assign slot: {message}', ['message' => $e->getMessage()]));
            }
        }

        return $this->render('pager-management/edit-slot.html.twig', [
            'pager' => $pager,
            'iSlot' => $index,
            'form' => $form,
        ]);
    }
}
