<?php

declare(strict_types=1);

namespace App\View\Web;

use App\Core\Contracts\Bus\CommandBus;
use App\Core\Contracts\Bus\QueryBus;
use App\Core\UserManagement\Command\EditUser;
use App\Core\UserManagement\Model\User;
use App\Core\UserManagement\Query\UserById;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use function Symfony\Component\Translation\t;

#[Route('/userinfo', name: 'web_userinfo')]
#[IsGranted('ROLE_USER')]
final class UserInfoController extends AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly CommandBus $commandBus,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $user = $this->queryBus->get(UserById::withId((int) $currentUser->getId()));
        if (null === $user) {
            throw $this->createNotFoundException('User not found');
        }

        $passwordForm = null;
        if ($user->hasPassword) {
            $passwordForm = $this->createFormBuilder()
                ->add('current_password', PasswordType::class, [
                    'label' => 'Current Password',
                    'required' => true,
                ])
                ->add('password', RepeatedType::class, [
                    'type' => PasswordType::class,
                    'invalid_message' => 'Passwords must match',
                    'required' => true,
                    'first_options' => ['label' => 'New Password', 'required' => true],
                    'second_options' => ['label' => 'Repeat New Password', 'required' => true],
                ])
                ->add('save', SubmitType::class, ['label' => 'Change Password'])
                ->getForm();

            $passwordForm->handleRequest($request);
            if ($passwordForm->isSubmitted() && $passwordForm->isValid()) {
                /** @var array{current_password: string, password: string} $data */
                $data = $passwordForm->getData();

                if (!password_verify($data['current_password'], $currentUser->getPassword())) {
                    $this->addFlash('error', t('Current password is incorrect'));
                } elseif ('' === $data['password']) {
                    $this->addFlash('error', t('New password cannot be empty'));
                } else {
                    try {
                        $this->commandBus->do(EditUser::with(
                            $user->username,
                            $data['password'],
                            null,
                            null,
                            null,
                        ));

                        $this->addFlash('success', t('Password changed successfully'));

                        return $this->redirectToRoute('web_userinfo');
                    } catch (RuntimeException $e) {
                        $this->addFlash('error', t('Failed to change password: {message}', ['message' => $e->getMessage()]));
                    }
                }
            }
        }

        return $this->render('userinfo.html.twig', [
            'user' => $user,
            'password_form' => $passwordForm,
        ]);
    }
}
