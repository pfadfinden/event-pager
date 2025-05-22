<?php

declare(strict_types=1);

namespace App\Tests\Core\IntelPage\Model;

use App\Core\IntelPage\Model\RecipientConfiguration;
use App\Core\TransportContract\Model\Priority;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[CoversClass(RecipientConfiguration::class)]
#[Group('unit')]
final class RecipientConfigurationTest extends TestCase
{
    public function testRecipientCanBeConfiguredToSendToPager(): void
    {
        $recipientConfiguration = new RecipientConfiguration([
            // leave empty, app will look for pager carried by the recipient
        ]);

        self::assertFalse($recipientConfiguration->hasChannelConfiguration());
    }

    public function testRecipientCanBeConfiguredToSendToChannel(): void
    {
        $id = Ulid::generate();

        $recipientConfiguration = new RecipientConfiguration([
            'channel' => $id,
        ]);

        self::assertTrue($recipientConfiguration->hasChannelConfiguration());
        self::assertEquals($id, $recipientConfiguration->channelId());
    }

    /**
     * @param array<string, int> $config
     */
    #[DataProvider('priorityConfigsProvider')]
    public function testAlertFromPriority(array $config, Priority $result): void
    {
        $recipientConfiguration = new RecipientConfiguration($config);

        self::assertEquals($result, $recipientConfiguration->alertFromPriority());
    }

    /**
     * @return array<string, array<array<string, int>|Priority>>
     */
    public static function priorityConfigsProvider(): array
    {
        return [
            'default' => [[], Priority::HIGH],
            'lower' => [['alert_from_priority' => 30], Priority::DEFAULT],
            'unknown' => [['alert_from_priority' => 35], Priority::HIGH],
        ];
    }
}
