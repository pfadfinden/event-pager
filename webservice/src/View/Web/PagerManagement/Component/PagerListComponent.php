<?php

declare(strict_types=1);

namespace App\View\Web\PagerManagement\Component;

use App\Core\Contracts\Bus\QueryBus;
use App\Core\IntelPage\Query\AllPager;
use App\Core\IntelPage\Query\CountOfPager;
use App\Core\IntelPage\ReadModel\Pager;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('PagerList', template: 'pager-management/_component/pager-list.html.twig')]
class PagerListComponent
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

    public function getMaxPager(): int
    {
        $query = null !== $this->q
            ? CountOfPager::withTextSearch($this->q)
            : CountOfPager::withoutFilter();

        return $this->queryBus->get($query);
    }

    public function getMaxPages(): float
    {
        return ceil($this->getMaxPager() / ($this->perPage ?? 10));
    }

    /**
     * @return iterable<Pager>
     */
    public function getPager(): iterable
    {
        $query = null !== $this->q
            ? AllPager::withTextSearch($this->q, $this->page, $this->perPage)
            : AllPager::withoutFilter($this->page, $this->perPage);

        return $this->queryBus->get($query);
    }
}
