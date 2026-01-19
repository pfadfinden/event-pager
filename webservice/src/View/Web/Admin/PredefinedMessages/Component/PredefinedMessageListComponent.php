<?php

declare(strict_types=1);

namespace App\View\Web\Admin\PredefinedMessages\Component;

use App\Core\Contracts\Bus\QueryBus;
use App\Core\PredefinedMessages\Query\CountPredefinedMessages;
use App\Core\PredefinedMessages\Query\ListPredefinedMessages;
use App\Core\PredefinedMessages\ReadModel\PredefinedMessageListEntry;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('PredefinedMessageList', template: 'admin/predefined-messages/_component/predefined-message-list.html.twig')]
class PredefinedMessageListComponent
{
    use DefaultActionTrait;

    #[LiveProp(writable: true, url: true)]
    public ?string $q = null;

    #[LiveProp(writable: true, url: true)]
    public ?int $page = 1;

    #[LiveProp(writable: true, url: true)]
    public ?int $perPage = 10;

    public function __construct(private readonly QueryBus $queryBus)
    {
    }

    public function getMaxMessages(): int
    {
        return $this->queryBus->get($this->buildCountQuery());
    }

    public function getMaxPages(): float
    {
        return ceil($this->getMaxMessages() / ($this->perPage ?? 10));
    }

    /**
     * @return iterable<PredefinedMessageListEntry>
     */
    public function getMessages(): iterable
    {
        return $this->queryBus->get($this->buildListQuery());
    }

    private function buildListQuery(): ListPredefinedMessages
    {
        $textFilter = (null !== $this->q && '' !== $this->q) ? $this->q : null;

        return ListPredefinedMessages::all($textFilter, $this->page, $this->perPage);
    }

    private function buildCountQuery(): CountPredefinedMessages
    {
        $textFilter = (null !== $this->q && '' !== $this->q) ? $this->q : null;

        return CountPredefinedMessages::all($textFilter);
    }
}
