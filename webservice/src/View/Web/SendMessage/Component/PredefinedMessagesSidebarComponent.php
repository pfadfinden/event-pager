<?php

declare(strict_types=1);

namespace App\View\Web\SendMessage\Component;

use App\Core\Contracts\Bus\QueryBus;
use App\Core\PredefinedMessages\Query\ListFavoritePredefinedMessages;
use App\Core\PredefinedMessages\Query\ListPredefinedMessages;
use App\Core\PredefinedMessages\ReadModel\PredefinedMessageListEntry;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('PredefinedMessagesSidebar', template: 'send_message/_component/predefined-messages-sidebar.html.twig')]
class PredefinedMessagesSidebarComponent
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public ?string $q = null;

    public function __construct(private readonly QueryBus $queryBus)
    {
    }

    /**
     * @return iterable<PredefinedMessageListEntry>
     */
    public function getFavorites(): iterable
    {
        return $this->queryBus->get(ListFavoritePredefinedMessages::topNine());
    }

    /**
     * @return iterable<PredefinedMessageListEntry>
     */
    public function getSearchResults(): iterable
    {
        if (null === $this->q || '' === $this->q) {
            return [];
        }

        return $this->queryBus->get(ListPredefinedMessages::withTextSearch($this->q, 1, 10));
    }

    public function hasSearchQuery(): bool
    {
        return null !== $this->q && '' !== $this->q;
    }

    /**
     * @return iterable<PredefinedMessageListEntry>
     */
    public function getMessages(): iterable
    {
        if ($this->hasSearchQuery()) {
            return $this->getSearchResults();
        }

        return $this->getFavorites();
    }
}
