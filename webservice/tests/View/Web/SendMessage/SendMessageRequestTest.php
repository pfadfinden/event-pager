<?php

declare(strict_types=1);

namespace App\Tests\View\Web\SendMessage;

use App\Tests\TestUtilities\FormValidationTrait;
use App\View\Web\SendMessage\SendMessageRecipientRequest;
use App\View\Web\SendMessage\SendMessageRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Form\Test\TypeTestCase;

#[CoversClass(SendMessageRequest::class)]
#[Group('unit')]
final class SendMessageRequestTest extends TypeTestCase
{
    use FormValidationTrait;

    public function testToIds(): void
    {
        $sut = new SendMessageRequest();
        $sut->message = 'hello world';
        $sut->priority = 1;
        $sut->to = array_map(function ($r) {
            $recipientA = new SendMessageRecipientRequest();
            $recipientA->id = $r['id'];
            $recipientA->label = $r['label'];
            $recipientA->type = $r['type'];

            return $recipientA;
        }, [['id' => '01J6YT42VYK4FWMPSCX2W6EQ2W', 'label' => 'Sample', 'type' => 'GROUP'], ['id' => '01J6YT42VYK4DWMPSCX2W6EQ2W', 'label' => 'Sample 1', 'type' => 'GROUP']]);

        self::assertSame(['01J6YT42VYK4FWMPSCX2W6EQ2W', '01J6YT42VYK4DWMPSCX2W6EQ2W'], $sut->toIds());
    }
}
