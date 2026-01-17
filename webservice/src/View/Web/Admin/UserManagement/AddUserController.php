<?php

declare(strict_types=1);

namespace App\View\Web\Admin\UserManagement;

use App\Core\Contracts\Bus\CommandBus;
use App\Core\UserManagement\Command\AddUser;
use App\View\Web\Admin\UserManagement\Request\AddUserRequest;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use function Symfony\Component\Translation\t;

#[Route('/admin/user/add', name: 'web_admin_user_add')]
#[IsGranted('ROLE_USERMANAGEMENT_ADDUSER')]
final class AddUserController extends AbstractController
{
    public function __construct(private readonly CommandBus $commandBus)
    {
    }

    public function __invoke(Request $request): Response
    {
        $userRequest = new AddUserRequest();
        $form = $this->createFormBuilder($userRequest)
            ->add('username', TextType::class, ['label' => 'Username'])
            ->add('displayname', TextType::class, ['label' => 'Display Name', 'required' => false])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Passwords must match',
                'first_options' => ['label' => 'Password'],
                'second_options' => ['label' => 'Repeat Password'],
            ])
            ->add('save', SubmitType::class, ['label' => 'Add User'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var AddUserRequest $userRequest */
            $userRequest = $form->getData();

            try {
                $this->commandBus->do(AddUser::with(
                    $userRequest->username,
                    $userRequest->password,
                    $userRequest->displayname ?? '',
                ));

                $this->addFlash('success', t('User created successfully'));

                return $this->redirectToRoute('web_admin_user_overview');
            } catch (InvalidArgumentException $e) {
                $this->addFlash('error', t('Failed to create user: {message}', ['message' => $e->getMessage()]));
            }
        }

        return $this->render('admin/user/add.html.twig', [
            'form' => $form,
        ]);
    }
}
