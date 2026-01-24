<?php

declare(strict_types=1);

namespace App\View\Web\SendMessage\Component;

use App\Core\Contracts\Bus\QueryBus;
use App\Core\SendMessage\Query\ListMessageHistory;
use App\Core\SendMessage\ReadModel\MessageHistoryEntry;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('RecentSentMessages', template: 'send_message/_component/recent-sent-messages.html.twig')]
class RecentSentMessagesComponent
{
    use DefaultActionTrait;

    private const LIMIT = 10;

    public function __construct(
        private readonly QueryBus $queryBus,
    ) {
    }

    /**
     * @return iterable<MessageHistoryEntry>
     */
    public function getMessages(): iterable
    {
        // TODO: Map authenticated user to their ULID once user-ULID mapping is implemented
        $userId = '01JNAY9HWQTEX1T45VBM2HG1XJ';

        return $this->queryBus->get(
            ListMessageHistory::forUser($userId, null, null, self::LIMIT)
        );
    }
}
