<?php

declare(strict_types=1);

namespace App\View\Web\RecipientManagement\Component;

use App\Core\Contracts\Bus\QueryBus;
use App\Core\MessageRecipient\Query\CountOfMessageRecipients;
use App\Core\MessageRecipient\Query\ListOfMessageRecipients;
use App\Core\MessageRecipient\ReadModel\RecipientListEntry;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('RecipientList', template: 'recipient-management/_component/recipient-list.html.twig')]
class RecipientListComponent
{
    use DefaultActionTrait;

    #[LiveProp(writable: true, url: true)]
    public ?string $q = null;

    #[LiveProp(writable: true, url: true)]
    public ?int $page = 1;

    #[LiveProp(writable: true, url: true)]
    public ?int $perPage = 10;

    #[LiveProp(writable: true, url: true)]
    public ?string $type = null;

    public function __construct(private readonly QueryBus $queryBus)
    {
    }

    public function getMaxRecipients(): int
    {
        return $this->queryBus->get($this->buildCountQuery());
    }

    public function getMaxPages(): float
    {
        return ceil($this->getMaxRecipients() / ($this->perPage ?? 10));
    }

    /**
     * @return iterable<RecipientListEntry>
     */
    public function getRecipients(): iterable
    {
        return $this->queryBus->get($this->buildListQuery());
    }

    private function buildListQuery(): ListOfMessageRecipients
    {
        $textFilter = (null !== $this->q && '' !== $this->q) ? $this->q : null;

        return match ($this->type) {
            'person' => ListOfMessageRecipients::onlyPeople($textFilter, $this->page, $this->perPage),
            'group' => ListOfMessageRecipients::onlyGroups($textFilter, $this->page, $this->perPage),
            'role' => ListOfMessageRecipients::onlyRoles($textFilter, $this->page, $this->perPage),
            default => ListOfMessageRecipients::all($textFilter, $this->page, $this->perPage),
        };
    }

    private function buildCountQuery(): CountOfMessageRecipients
    {
        $textFilter = (null !== $this->q && '' !== $this->q) ? $this->q : null;

        return match ($this->type) {
            'person' => CountOfMessageRecipients::onlyPeople($textFilter),
            'group' => CountOfMessageRecipients::onlyGroups($textFilter),
            'role' => CountOfMessageRecipients::onlyRoles($textFilter),
            default => CountOfMessageRecipients::all($textFilter),
        };
    }
}
