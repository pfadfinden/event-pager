<?php

declare(strict_types=1);

namespace App\View\Web\Admin\UserManagement;

use App\Core\Contracts\Bus\CommandBus;
use App\Core\Contracts\Bus\QueryBus;
use App\Core\UserManagement\Command\DeleteUser;
use App\Core\UserManagement\Query\UserById;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use function Symfony\Component\Translation\t;

#[Route('/admin/user/{id}', name: 'web_admin_user_details')]
#[IsGranted('ROLE_USERMANAGEMENT_VIEW')]
final class UserDetailsController extends AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly CommandBus $commandBus,
    ) {
    }

    public function __invoke(Request $request, int $id): Response
    {
        $user = $this->queryBus->get(UserById::withId($id));
        if (null === $user) {
            throw new NotFoundHttpException('User not found');
        }

        $deleteForm = $this->createFormBuilder()->add('delete', SubmitType::class, ['label' => 'Delete'])->getForm();
        $deleteForm->handleRequest($request);
        if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
            $this->denyAccessUnlessGranted('ROLE_USERMANAGEMENT_DELETEUSER');

            try {
                $this->commandBus->do(DeleteUser::with($user->username));
                $this->addFlash('success', t('User deleted successfully'));

                return $this->redirectToRoute('web_admin_user_overview');
            } catch (RuntimeException $e) {
                $this->addFlash('error', t('Failed to delete user: {message}', ['message' => $e->getMessage()]));
            }
        }

        return $this->render('admin/user/show.html.twig', [
            'user' => $user,
            'delete_form' => $deleteForm,
        ]);
    }
}
