<?php

declare(strict_types=1);

namespace App\View\Web\Admin\PredefinedMessages\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class EditPredefinedMessageRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 100)]
    public string $title = '';

    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 255)]
    public string $messageContent = '';

    #[Assert\NotBlank]
    #[Assert\Range(min: 1, max: 5)]
    public int $priority = 3;

    #[Assert\NotBlank]
    public string $recipientIds = '';

    public bool $isFavorite = false;

    #[Assert\PositiveOrZero]
    public int $sortOrder = 0;

    public bool $isEnabled = true;

    /**
     * @return list<string>
     */
    public function getRecipientIdsAsArray(): array
    {
        if ('' === trim($this->recipientIds)) {
            return [];
        }

        $ids = array_map('trim', explode(',', $this->recipientIds));

        return array_values(array_filter($ids, static fn (string $id): bool => '' !== $id));
    }
}
