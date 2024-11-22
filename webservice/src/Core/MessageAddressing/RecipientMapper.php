<?php

declare(strict_types=1);

namespace App\Core\MessageAddressing;

use function iterator_to_array;


class RecipientMapper
{
    public function __construct(
        /**
         * @var list<TransportAddresser>
         */
        private array $transportAddressers
    ) {
    }

    /**
     * @param list<MessageRecipient> $recipients
     *
     * @return list<MessageRecipient>
     */
    public function sendTo(array $recipients, Priority $priority, string $message): array
    {
        $map = new RecipientSuccessMap();

        /** @var TransportAddresser $addresser */
        foreach ($this->transportAddressers as $addresser) {

            foreach ($addresser->sendTo($recipients, $priority, $message) as $recipient => $success) {
                $map->addIfMoreSuccessful($recipient, $success);
            }
        }

        return iterator_to_array($map->getReachableRecipients(), false);
    }
}
