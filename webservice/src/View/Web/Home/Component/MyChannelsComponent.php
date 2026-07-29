<?php

declare(strict_types=1);

namespace App\View\Web\Home\Component;

use App\Core\Contracts\Bus\QueryBus;
use App\Core\MessageRecipient\Query\MessageRecipientById;
use App\Core\MessageRecipient\ReadModel\RecipientDetail;
use App\Core\UserManagement\Model\User;
use App\Core\UserManagement\Query\UserById;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use function assert;

#[AsLiveComponent('MyChannels', template: 'home/_component/my-channels.html.twig')]
class MyChannelsComponent
{
    use DefaultActionTrait;

    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly Security $security,
    ) {
    }

    public function getRecipient(): ?RecipientDetail
    {
        $user = $this->security->getUser();
        assert($user instanceof User);

        $userDetail = $this->queryBus->get(UserById::withId($user->getId()->toString()));
        if (null === $userDetail?->recipientId) {
            return null;
        }

        return $this->queryBus->get(MessageRecipientById::withId($userDetail->recipientId));
    }
}
