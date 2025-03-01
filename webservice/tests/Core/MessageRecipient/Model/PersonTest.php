<?php

declare(strict_types=1);

namespace App\Tests\Core\MessageRecipient\Model;

use App\Core\MessageRecipient\Model\Person;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[CoversClass(Person::class)]
#[Small()]
final class PersonTest extends TestCase
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
        $person = new Person($name, $id);

        if (null === $id) {
            $person->id->toString();
        } else {
            self::assertSame($id, $person->id);
        }
        self::assertSame($name, $person->getName());
        self::assertSame([], $person->getRoles());
    }
}
