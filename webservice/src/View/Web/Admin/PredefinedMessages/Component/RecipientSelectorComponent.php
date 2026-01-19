<?php

declare(strict_types=1);

namespace App\View\Web\Admin\PredefinedMessages\Component;

use App\Core\Contracts\Bus\QueryBus;
use App\Core\MessageRecipient\Query\ListOfMessageRecipients;
use App\Core\MessageRecipient\Query\MessageRecipientById;
use App\Core\MessageRecipient\ReadModel\RecipientListEntry;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use function in_array;

#[AsLiveComponent('RecipientSelector', template: 'admin/predefined-messages/_component/recipient-selector.html.twig')]
class RecipientSelectorComponent
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $search = '';

    /**
     * @var list<array{id: string, name: string, type: string}>
     */
    #[LiveProp(writable: true)]
    public array $selectedRecipients = [];

    #[LiveProp]
    public string $fieldName = 'recipientIds';

    public function __construct(private readonly QueryBus $queryBus)
    {
    }

    /**
     * @return list<RecipientListEntry>
     */
    public function getSearchResults(): array
    {
        if ('' === $this->search) {
            return [];
        }

        $results = $this->queryBus->get(ListOfMessageRecipients::all($this->search, 1, 20));

        // Filter out already selected recipients
        $selectedIds = array_column($this->selectedRecipients, 'id');

        $filtered = [];
        foreach ($results as $result) {
            if (!in_array($result->id, $selectedIds, true)) {
                $filtered[] = $result;
            }
        }

        return $filtered;
    }

    #[LiveAction]
    public function addRecipient(#[LiveArg] string $id): void
    {
        // Check if already selected
        foreach ($this->selectedRecipients as $recipient) {
            if ($recipient['id'] === $id) {
                return;
            }
        }

        // Fetch recipient details
        $recipient = $this->queryBus->get(MessageRecipientById::withId($id));
        if (null === $recipient) {
            return;
        }

        $this->selectedRecipients[] = [
            'id' => $recipient->id,
            'name' => $recipient->name,
            'type' => $recipient->type,
        ];

        $this->search = '';
    }

    #[LiveAction]
    public function removeRecipient(#[LiveArg] string $id): void
    {
        $this->selectedRecipients = array_values(array_filter(
            $this->selectedRecipients,
            static fn (array $r): bool => $r['id'] !== $id,
        ));
    }

    /**
     * @return list<string>
     */
    public function getSelectedIds(): array
    {
        return array_column($this->selectedRecipients, 'id');
    }
}
