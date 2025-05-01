<?php

declare(strict_types=1);

namespace App\View\Web\SendMessage\Form;

use App\Core\Contracts\Bus\QueryBus;
use App\Core\MessageRecipient\Query\ListOfMessageRecipients;
use App\Core\MessageRecipient\ReadModel\RecipientListEntry;
use Symfony\Component\Form\ChoiceList\Loader\AbstractChoiceLoader;

/**
 * Required for a form rendering a list of recipient choices.
 */
final class GroupsOnlyRecipientsChoiceLoader extends AbstractChoiceLoader implements RecipientsChoiceLoader
{
    public function __construct(private readonly QueryBus $queryBus)
    {
    }

    /**
     * @return iterable<RecipientListEntry>
     */
    protected function loadChoices(): iterable
    {
        return $this->queryBus->get(ListOfMessageRecipients::onlyGroups());
    }
}
