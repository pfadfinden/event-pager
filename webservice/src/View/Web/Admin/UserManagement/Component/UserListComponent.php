<?php

declare(strict_types=1);

namespace App\View\Web\Admin\UserManagement\Component;

use App\Core\Contracts\Bus\QueryBus;
use App\Core\UserManagement\Query\CountUsers;
use App\Core\UserManagement\Query\ListUsers;
use App\Core\UserManagement\ReadModel\UserListEntry;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('UserList', template: 'admin/user/_component/user-list.html.twig')]
class UserListComponent
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

    public function getMaxUsers(): int
    {
        return $this->queryBus->get($this->buildCountQuery());
    }

    public function getMaxPages(): float
    {
        return ceil($this->getMaxUsers() / ($this->perPage ?? 10));
    }

    /**
     * @return iterable<UserListEntry>
     */
    public function getUsers(): iterable
    {
        return $this->queryBus->get($this->buildListQuery());
    }

    private function buildListQuery(): ListUsers
    {
        $textFilter = (null !== $this->q && '' !== $this->q) ? $this->q : null;

        return ListUsers::all($textFilter, $this->page, $this->perPage);
    }

    private function buildCountQuery(): CountUsers
    {
        $textFilter = (null !== $this->q && '' !== $this->q) ? $this->q : null;

        return CountUsers::all($textFilter);
    }
}
