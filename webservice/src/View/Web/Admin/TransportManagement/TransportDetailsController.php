<?php

declare(strict_types=1);

namespace App\View\Web\Admin\TransportManagement;

use App\Core\Contracts\Bus\CommandBus;
use App\Core\Contracts\Bus\QueryBus;
use App\Core\TransportManager\Command\RemoveTransportConfiguration;
use App\Core\TransportManager\Query\Transport;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use function Symfony\Component\Translation\t;

#[Route('/admin/transport/{key}', name: 'web_admin_transport_details')]
#[IsGranted('ROLE_TRANSPORT_CONFIGURATION_VIEWER')]
final class TransportDetailsController extends AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly CommandBus $commandBus,
    ) {
    }

    public function __invoke(Request $request, string $key): Response
    {
        $transport = $this->queryBus->get(Transport::withKey($key));
        if (null === $transport) {
            throw new NotFoundHttpException('Transport not found');
        }

        $deleteForm = $this->createFormBuilder()->add('delete', SubmitType::class, ['label' => 'Delete'])->getForm();
        $deleteForm->handleRequest($request);
        if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
            $this->denyAccessUnlessGranted('ROLE_TRANSPORT_ADMINISTRATOR');

            try {
                $this->commandBus->do(new RemoveTransportConfiguration($key));
                $this->addFlash('success', t('Transport deleted successfully'));

                return $this->redirectToRoute('web_admin_home');
            } catch (RuntimeException $e) {
                $this->addFlash('error', t('Failed to delete transport: {message}', ['message' => $e->getMessage()]));
            }
        }

        return $this->render('admin/transport/show.html.twig', [
            'transport' => $transport,
            'delete_form' => $deleteForm,
        ]);
    }
}
