<?php

declare(strict_types=1);

namespace App\View\Web\MessageHistory\Component;

use App\Core\Contracts\Bus\QueryBus;
use App\Core\SendMessage\Query\GetOutgoingMessagesForIncoming;
use App\Core\SendMessage\ReadModel\OutgoingMessageDetail;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('MessageHistoryDetail', template: 'message-history/_component/message-history-detail.html.twig')]
class MessageHistoryDetailComponent
{
    use DefaultActionTrait;

    #[LiveProp]
    public string $incomingMessageId = '';

    public function __construct(
        private readonly QueryBus $queryBus,
    ) {
    }

    /**
     * @return iterable<OutgoingMessageDetail>
     */
    public function getOutgoingMessages(): iterable
    {
        if ('' === $this->incomingMessageId) {
            return [];
        }

        return $this->queryBus->get(new GetOutgoingMessagesForIncoming($this->incomingMessageId));
    }
}
