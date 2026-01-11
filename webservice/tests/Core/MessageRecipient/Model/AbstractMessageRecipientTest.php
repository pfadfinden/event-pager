<?php

declare(strict_types=1);

namespace App\Tests\Core\MessageRecipient\Model;

use App\Core\MessageRecipient\Model\AbstractMessageRecipient;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[CoversClass(AbstractMessageRecipient::class)]
#[Small()]
final class AbstractMessageRecipientTest extends TestCase
{
    /**
     * @return array{0: string, 1: ?Ulid}[]
     */
    public static function constructorProvider(): array
    {
        return [
            ['Dustin', null],
            ['Nilpferd', new Ulid()],
        ];
    }

    #[DataProvider('constructorProvider')]
    public function testConstructor(string $name, ?Ulid $id): void
    {
        $recipient = new class($name, $id) extends AbstractMessageRecipient {};

        if (null === $id) {
            $recipient->getId()->toString();
        } else {
            self::assertSame($id, $recipient->getId());
        }
        self::assertSame($name, $recipient->getName());
        self::assertSame([], $recipient->getGroups());
    }

    public function testCanChangeName(): void
    {
        $recipient = new class('Previous Name') extends AbstractMessageRecipient {};
        $recipient->setName('New Name');

        self::assertSame('New Name', $recipient->getName());
    }
}
