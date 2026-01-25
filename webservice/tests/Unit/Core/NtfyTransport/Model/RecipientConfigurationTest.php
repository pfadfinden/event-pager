<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\NtfyTransport\Model;

use App\Core\NtfyTransport\Model\RecipientConfiguration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RecipientConfiguration::class)]
final class RecipientConfigurationTest extends TestCase
{
    public function testHasTopicReturnsTrueWhenTopicIsConfigured(): void
    {
        $config = new RecipientConfiguration(['topic' => 'my-alerts']);

        self::assertTrue($config->hasTopic());
    }

    public function testHasTopicReturnsFalseWhenTopicIsEmpty(): void
    {
        $config = new RecipientConfiguration(['topic' => '']);

        self::assertFalse($config->hasTopic());
    }

    public function testHasTopicReturnsFalseWhenTopicIsMissing(): void
    {
        $config = new RecipientConfiguration([]);

        self::assertFalse($config->hasTopic());
    }

    public function testTopicReturnsConfiguredValue(): void
    {
        $config = new RecipientConfiguration(['topic' => 'my-alerts']);

        self::assertSame('my-alerts', $config->topic());
    }
}
