<?php

declare(strict_types=1);

namespace App\Tests\Core\PredefinedMessages\Model;

use App\Core\PredefinedMessages\Model\PredefinedMessage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[Group('unit')]
#[CoversClass(PredefinedMessage::class)]
final class PredefinedMessageTest extends TestCase
{
    public function testCreatePredefinedMessage(): void
    {
        $message = new PredefinedMessage(
            'Fire Alert',
            'Fire detected in building',
            5,
            ['01JNAY9HWQTEX1T45VBM2HG1XJ'],
            true,
            1,
            true,
        );

        self::assertSame('Fire Alert', $message->getTitle());
        self::assertSame('Fire detected in building', $message->getMessageContent());
        self::assertSame(5, $message->getPriority());
        self::assertSame(['01JNAY9HWQTEX1T45VBM2HG1XJ'], $message->getRecipientIds());
        self::assertTrue($message->isFavorite());
        self::assertSame(1, $message->getSortOrder());
        self::assertTrue($message->isEnabled());
    }

    public function testCreatePredefinedMessageWithCustomId(): void
    {
        $customId = new Ulid();
        $message = new PredefinedMessage(
            'Test',
            'Test content',
            3,
            [],
            false,
            0,
            true,
            $customId,
        );

        self::assertSame($customId, $message->getId());
    }

    public function testSetTitle(): void
    {
        $message = new PredefinedMessage('Original', 'Content', 3, []);
        $message->setTitle('Updated');

        self::assertSame('Updated', $message->getTitle());
    }

    public function testSetMessageContent(): void
    {
        $message = new PredefinedMessage('Title', 'Original', 3, []);
        $message->setMessageContent('Updated content');

        self::assertSame('Updated content', $message->getMessageContent());
    }

    public function testSetPriority(): void
    {
        $message = new PredefinedMessage('Title', 'Content', 3, []);
        $message->setPriority(5);

        self::assertSame(5, $message->getPriority());
    }

    public function testSetRecipientIds(): void
    {
        $message = new PredefinedMessage('Title', 'Content', 3, []);
        $message->setRecipientIds(['01JNAY9HWQTEX1T45VBM2HG1XJ', '01JNAY9HWQTEX1T45VBM2HG1XK']);

        self::assertSame(['01JNAY9HWQTEX1T45VBM2HG1XJ', '01JNAY9HWQTEX1T45VBM2HG1XK'], $message->getRecipientIds());
    }

    public function testSetIsFavorite(): void
    {
        $message = new PredefinedMessage('Title', 'Content', 3, [], false);
        $message->setIsFavorite(true);

        self::assertTrue($message->isFavorite());
    }

    public function testSetSortOrder(): void
    {
        $message = new PredefinedMessage('Title', 'Content', 3, [], false, 0);
        $message->setSortOrder(5);

        self::assertSame(5, $message->getSortOrder());
    }

    public function testSetIsEnabled(): void
    {
        $message = new PredefinedMessage('Title', 'Content', 3, [], false, 0, true);
        $message->setIsEnabled(false);

        self::assertFalse($message->isEnabled());
    }
}
