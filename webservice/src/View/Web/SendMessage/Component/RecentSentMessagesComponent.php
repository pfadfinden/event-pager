<?php

declare(strict_types=1);

namespace App\View\Web\SendMessage\Component;

use App\Core\Contracts\Bus\QueryBus;
use App\Core\SendMessage\Query\ListMessageHistory;
use App\Core\SendMessage\ReadModel\MessageHistoryEntry;
use App\Core\UserManagement\Model\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use function assert;

#[AsLiveComponent('RecentSentMessages', template: 'send_message/_component/recent-sent-messages.html.twig')]
class RecentSentMessagesComponent
{
    use DefaultActionTrait;

    /**
     * Refresh the component when a message is sent.
     */
    #[LiveListener('messageSent')]
    public function onMessageSent(): void
    {
        // The component will re-render automatically after this method is called
    }

    private const LIMIT = 10;

    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly Security $security,
    ) {
    }

    /**
     * @return iterable<MessageHistoryEntry>
     */
    public function getMessages(): iterable
    {
        $user = $this->security->getUser();
        assert($user instanceof User);

        return $this->queryBus->get(
            ListMessageHistory::forUser($user->getId()->toString(), null, null, self::LIMIT)
        );
    }
}
