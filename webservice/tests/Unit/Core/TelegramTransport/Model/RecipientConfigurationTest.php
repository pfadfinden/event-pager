<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\TelegramTransport\Model;

use App\Core\TelegramTransport\Model\RecipientConfiguration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RecipientConfiguration::class)]
final class RecipientConfigurationTest extends TestCase
{
    public function testHasChatIdReturnsTrueWhenChatIdExists(): void
    {
        $config = new RecipientConfiguration(['chatId' => '123456789']);

        self::assertTrue($config->hasChatId());
    }

    public function testHasChatIdReturnsFalseWhenChatIdIsMissing(): void
    {
        $config = new RecipientConfiguration([]);

        self::assertFalse($config->hasChatId());
    }

    public function testHasChatIdReturnsFalseWhenChatIdIsEmpty(): void
    {
        $config = new RecipientConfiguration(['chatId' => '']);

        self::assertFalse($config->hasChatId());
    }

    public function testChatIdReturnsChatIdValue(): void
    {
        $config = new RecipientConfiguration(['chatId' => '123456789']);

        self::assertSame('123456789', $config->chatId());
    }

    public function testChatIdWorksWithNegativeGroupId(): void
    {
        $config = new RecipientConfiguration(['chatId' => '-1001234567890']);

        self::assertTrue($config->hasChatId());
        self::assertSame('-1001234567890', $config->chatId());
    }
}
