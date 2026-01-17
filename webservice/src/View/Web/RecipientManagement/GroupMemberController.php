<?php

declare(strict_types=1);

namespace App\View\Web\RecipientManagement;

use App\Core\Contracts\Bus\CommandBus;
use App\Core\Contracts\Bus\QueryBus;
use App\Core\MessageRecipient\Command\RemoveRecipientFromGroup;
use App\Core\MessageRecipient\Query\MessageRecipientById;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use function Symfony\Component\Translation\t;

#[Route('/recipients/group/{id}/members/{memberId}/remove', name: 'web_recipient_management_group_member_remove', methods: ['POST'])]
#[IsGranted('ROLE_MANAGE_RECIPIENT_GROUPS')]
final class GroupMemberController extends AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly CommandBus $commandBus,
    ) {
    }

    public function __invoke(string $id, string $memberId): RedirectResponse
    {
        $recipient = $this->queryBus->get(MessageRecipientById::withId($id));
        if (null === $recipient || !$recipient->isGroup()) {
            throw new NotFoundHttpException('Group not found');
        }

        try {
            $this->commandBus->do(new RemoveRecipientFromGroup($recipient->id, $memberId));
            $this->addFlash('success', t('Member removed successfully'));
        } catch (RuntimeException $e) {
            $this->addFlash('error', t('Failed to remove member: {message}', ['message' => $e->getMessage()]));
        }

        return $this->redirectToRoute('web_recipient_management_group_details', ['id' => $recipient->id]);
    }
}
