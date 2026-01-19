<?php

declare(strict_types=1);

namespace App\Core\PredefinedMessages\Model;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity]
#[ORM\Table(name: 'predefined_message')]
class PredefinedMessage
{
    #[ORM\Column(type: UlidType::NAME)]
    #[ORM\Id]
    private Ulid $id;

    #[ORM\Column(length: 100)]
    private string $title;

    #[ORM\Column(length: 255)]
    private string $messageContent;

    #[ORM\Column(type: 'smallint')]
    private int $priority;

    /**
     * @var list<string>
     */
    #[ORM\Column(type: 'json')]
    private array $recipientIds;

    #[ORM\Column]
    private bool $isFavorite;

    #[ORM\Column]
    private int $sortOrder;

    #[ORM\Column]
    private bool $isEnabled;

    /**
     * @param list<string> $recipientIds
     */
    public function __construct(
        string $title,
        string $messageContent,
        int $priority,
        array $recipientIds,
        bool $isFavorite = false,
        int $sortOrder = 0,
        bool $isEnabled = true,
        ?Ulid $id = null,
    ) {
        $this->id = $id ?? new Ulid();
        $this->title = $title;
        $this->messageContent = $messageContent;
        $this->priority = $priority;
        $this->recipientIds = $recipientIds;
        $this->isFavorite = $isFavorite;
        $this->sortOrder = $sortOrder;
        $this->isEnabled = $isEnabled;
    }

    public function getId(): Ulid
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getMessageContent(): string
    {
        return $this->messageContent;
    }

    public function setMessageContent(string $messageContent): void
    {
        $this->messageContent = $messageContent;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    /**
     * @return list<string>
     */
    public function getRecipientIds(): array
    {
        return $this->recipientIds;
    }

    /**
     * @param list<string> $recipientIds
     */
    public function setRecipientIds(array $recipientIds): void
    {
        $this->recipientIds = $recipientIds;
    }

    public function isFavorite(): bool
    {
        return $this->isFavorite;
    }

    public function setIsFavorite(bool $isFavorite): void
    {
        $this->isFavorite = $isFavorite;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): void
    {
        $this->sortOrder = $sortOrder;
    }

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    public function setIsEnabled(bool $isEnabled): void
    {
        $this->isEnabled = $isEnabled;
    }
}
