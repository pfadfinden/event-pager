<?php

declare(strict_types=1);

namespace App\Core\IntelPage\Application;

use App\Core\IntelPage\Events\OutgoingMessageTransmissionFailed;
use App\Core\IntelPage\Events\OutgoingMessageTransmitted;
use App\Core\IntelPage\Model\PagerMessage;
use App\Core\IntelPage\Port\IntelPageTransmitterInterface;
use App\Core\IntelPage\Port\SendPagerMessageServiceInterface;
use Brick\DateTime\Instant;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use function assert;
use function sprintf;

final readonly class SendPagerMessageService implements SendPagerMessageServiceInterface
{
    private const int RETRY_LIMIT = 2;

    public function __construct(
        private EntityManagerInterface $em,
        private IntelPageTransmitterInterface $transmitter,
        private ?MessageBusInterface $eventBus = null,
        private ?LoggerInterface $logger = null,
    ) {
    }

    public function nextMessageToSend(string $transportKey): ?PagerMessage
    {
        $dql = sprintf(
            'SELECT m FROM %s m WHERE m.transport = :transport AND  m.attemptedToSend < %d AND m.transmittedOn is null AND m.queuedOn > :maxAge ORDER BY m.priority ASC, m.queuedOn ASC',
            PagerMessage::class,
            self::RETRY_LIMIT
        );
        $query = $this->em->createQuery($dql);
        $query->setCacheable(false);
        $query->setParameter('maxAge', Instant::now()->minusMinutes(5)->toDecimal());
        $query->setParameter('transport', $transportKey);
        $query->setMaxResults(1);

        $result = $query->getOneOrNullResult();
        assert(null === $result || $result instanceof PagerMessage, 'query should return correct result type');

        return $result;
    }

    public function send(PagerMessage $message): void
    {
        try {
            $this->transmitter->transmit($message->getCap(), $message->getMessage());
        } catch (Exception $e) {
            $this->handleTransmissionFailure($message, $e);

            throw $e; // rethrow to allow caller to abort
        }

        $this->markMessageSend($message);
        $this->logger?->debug(sprintf('Successfully send IntelPage PagerMessage "%s"', $message->getId()->toString()));
        $this->eventBus?->dispatch(new OutgoingMessageTransmitted($message->getId()->toString()));
    }

    private function markMessageSend(PagerMessage $message): void
    {
        $message->markSend();
        $this->em->persist($message);
        $this->em->flush();
    }

    private function markMessageFailed(PagerMessage $message): void
    {
        $message->failedToSend();
        $this->em->persist($message);
        $this->em->flush();
    }

    private function handleTransmissionFailure(PagerMessage $message, Exception $e): void
    {
        $this->markMessageFailed($message);

        $this->logger?->error(sprintf('Failed to send IntelPage PagerMessage "%s" (number of attempts: %d)', $message->getId()->toString(), $message->getAttemptedToSend()));

        if (self::RETRY_LIMIT === $message->getAttemptedToSend()) {
            // -- reached retry limit, do not retry again and mark as failed
            $this->eventBus?->dispatch(new OutgoingMessageTransmissionFailed($message->getId()->toString(), $e->getMessage()));
        }
    }
}
