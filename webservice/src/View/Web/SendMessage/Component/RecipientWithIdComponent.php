<?php

declare(strict_types=1);

namespace App\View\Web\SendMessage\Component;

use App\Core\Contracts\Bus\QueryBus;
use App\Core\MessageRecipient\Query\MessageRecipientWithId;
use App\Core\MessageRecipient\ReadModel\RecipientListEntry;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('RecipientWithId', template: 'send_message/_component/recipient-with-id.html.twig')]
final class RecipientWithIdComponent
{
    use DefaultActionTrait;

    public string $id;

    public function __construct(private readonly QueryBus $queryBus)
    {
    }

    public function getRecipient(): RecipientListEntry
    {
        return $this->queryBus->get(new MessageRecipientWithId($this->id));
    }
}
