<?php

declare(strict_types=1);

namespace App\View\Web\SendMessage;

use App\Core\Contracts\Bus\QueryBus;
use App\Core\MessageRecipient\Query\ListOfMessageRecipients;
use App\Core\MessageRecipient\ReadModel\RecipientListEntry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/send/_searchRecipients', name: 'send_message_search_recipients')]
final class SendMessageSearchRecipientsController extends AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus,
    ) {
    }

    public function __invoke(#[MapQueryParameter] string $search): JsonResponse
    {
        $data = $this->queryBus->get(ListOfMessageRecipients::all($search));

        $result = array_map(function (RecipientListEntry $r): RecipientListEntry {
            $prefix = match ($r->type) {
                'GROUP' => '👥 ', 'ROLE' => '💼 ', default => '👤 ',
            };
            $r->name = $prefix.$r->name;

            return $r;
        }, iterator_to_array($data));

        return new JsonResponse($result, Response::HTTP_OK);
    }
}
