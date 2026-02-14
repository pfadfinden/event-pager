<?php

declare(strict_types=1);

namespace App\View\Web\MessageHistory\Component;

use App\Core\Contracts\Bus\QueryBus;
use App\Core\SendMessage\Query\CountMessageHistory;
use App\Core\SendMessage\Query\ListMessageHistory;
use App\Core\SendMessage\ReadModel\MessageHistoryEntry;
use App\Core\UserManagement\Model\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use function assert;

#[AsLiveComponent('MessageHistoryList', template: 'message-history/_component/message-history-list.html.twig')]
class MessageHistoryListComponent
{
    use DefaultActionTrait;

    #[LiveProp(writable: true, url: true)]
    public ?string $q = null;

    #[LiveProp(writable: true, url: true)]
    public ?int $page = 1;

    #[LiveProp(writable: true, url: true)]
    public ?int $perPage = 10;

    #[LiveProp(writable: true, url: true)]
    public bool $showAll = false;

    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly Security $security,
    ) {
    }

    public function canViewAll(): bool
    {
        return $this->security->isGranted('ROLE_VIEW_MESSAGE_HISTORY_ALL');
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
     * @return iterable<MessageHistoryEntry>
     */
    public function getMessages(): iterable
    {
        return $this->queryBus->get($this->buildListQuery());
    }

    private function buildListQuery(): ListMessageHistory
    {
        $textFilter = (null !== $this->q && '' !== $this->q) ? $this->q : null;

        if ($this->showAll && $this->canViewAll()) {
            return ListMessageHistory::all($textFilter, $this->page, $this->perPage);
        }

        $userId = $this->getCurrentUserId();

        return ListMessageHistory::forUser($userId, $textFilter, $this->page, $this->perPage);
    }

    private function buildCountQuery(): CountMessageHistory
    {
        $textFilter = (null !== $this->q && '' !== $this->q) ? $this->q : null;

        if ($this->showAll && $this->canViewAll()) {
            return CountMessageHistory::all($textFilter);
        }

        $userId = $this->getCurrentUserId();

        return CountMessageHistory::forUser($userId, $textFilter);
    }

    private function getCurrentUserId(): string
    {
        $user = $this->security->getUser();
        assert($user instanceof User);

        return $user->getId()->toString();
    }
}
