<?php

declare(strict_types=1);

namespace App\View\Web\PagerManagement\Component;

use App\Core\Contracts\Bus\QueryBus;
use App\Core\IntelPage\Query\AllChannel;
use App\Core\IntelPage\Query\CountOfChannel;
use App\Core\IntelPage\ReadModel\Channel;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('ChannelList', template: 'pager-management/_component/channel-list.html.twig')]
class ChannelListComponent
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

    public function getMaxChannel(): int
    {
        $query = null !== $this->q
            ? CountOfChannel::withTextSearch($this->q)
            : CountOfChannel::withoutFilter();

        return $this->queryBus->get($query);
    }

    public function getMaxPages(): float
    {
        return ceil($this->getMaxChannel() / ($this->perPage ?? 10));
    }

    /**
     * @return iterable<Channel>
     */
    public function getChannel(): iterable
    {
        $query = null !== $this->q
            ? AllChannel::withTextSearch($this->q, $this->page, $this->perPage)
            : AllChannel::withoutFilter($this->page, $this->perPage);

        return $this->queryBus->get($query);
    }
}
