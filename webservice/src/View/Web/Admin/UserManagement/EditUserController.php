<?php

declare(strict_types=1);

namespace App\View\Web\Admin\UserManagement;

use App\Core\Contracts\Bus\CommandBus;
use App\Core\Contracts\Bus\QueryBus;
use App\Core\UserManagement\Command\EditUser;
use App\Core\UserManagement\Query\UserById;
use App\View\Web\Admin\UserManagement\Request\EditUserRequest;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use function Symfony\Component\Translation\t;

#[Route('/admin/user/{id}/edit', name: 'web_admin_user_edit')]
#[IsGranted('ROLE_USERMANAGEMENT_EDITUSER')]
final class EditUserController extends AbstractController
{
    private const ASSIGNABLE_ROLES = [
        'ROLE_USER' => 'User (View only)',
        'ROLE_ACTIVE_USER' => 'Active User (Send messages)',
        'ROLE_SUPPORT' => 'Support (Manage recipients)',
        'ROLE_MANAGER' => 'Manager (Full recipient management)',
    ];

    private const PRIVILEGED_ROLES = [
        'ROLE_ADMIN' => 'Administrator (Full access)',
    ];

    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly QueryBus $queryBus,
    ) {
    }

    public function __invoke(Request $request, int $id): Response
    {
        $user = $this->queryBus->get(UserById::withId($id));
        if (null === $user) {
            throw new NotFoundHttpException('User not found');
        }

        $userRequest = new EditUserRequest();
        $userRequest->displayname = $user->displayname;
        $userRequest->roles = $user->roles;

        $canAssignPrivileged = $this->isGranted('ROLE_USERMANAGEMENT_ASSIGN_PRIVILEGED');
        $availableRoles = self::ASSIGNABLE_ROLES;
        if ($canAssignPrivileged) {
            $availableRoles = array_merge($availableRoles, self::PRIVILEGED_ROLES);
        }

        $form = $this->createFormBuilder($userRequest)
            ->add('displayname', TextType::class, ['label' => 'Display Name', 'required' => false])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Passwords must match',
                'required' => false,
                'first_options' => ['label' => 'New Password', 'required' => false],
                'second_options' => ['label' => 'Repeat New Password', 'required' => false],
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Roles',
                'choices' => array_flip($availableRoles),
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('save', SubmitType::class, ['label' => 'Save'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var EditUserRequest $userRequest */
            $userRequest = $form->getData();

            // Calculate role changes
            $currentRoles = array_filter($user->roles, fn ($r) => 'ROLE_USER' !== $r);
            $newRoles = $userRequest->roles;

            // Filter out privileged roles if user doesn't have permission
            if (!$canAssignPrivileged) {
                $newRoles = array_filter($newRoles, fn ($r) => !isset(self::PRIVILEGED_ROLES[$r]));
                // Preserve existing privileged roles
                foreach ($currentRoles as $role) {
                    if (isset(self::PRIVILEGED_ROLES[$role])) {
                        $newRoles[] = $role;
                    }
                }
            }

            $addRoles = array_diff($newRoles, $currentRoles);
            $removeRoles = array_diff($currentRoles, $newRoles);

            try {
                $this->commandBus->do(EditUser::with(
                    $user->username,
                    '' !== ($userRequest->password ?? '') ? $userRequest->password : null,
                    $userRequest->displayname,
                    [] !== $addRoles ? array_values($addRoles) : null,
                    [] !== $removeRoles ? array_values($removeRoles) : null,
                ));

                $this->addFlash('success', t('User updated successfully'));

                return $this->redirectToRoute('web_admin_user_details', ['id' => $user->id]);
            } catch (RuntimeException $e) {
                $this->addFlash('error', t('Failed to update user: {message}', ['message' => $e->getMessage()]));
            }
        }

        return $this->render('admin/user/edit.html.twig', [
            'form' => $form,
            'user' => $user,
        ]);
    }
}
