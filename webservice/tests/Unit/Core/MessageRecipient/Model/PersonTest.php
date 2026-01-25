<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\MessageRecipient\Model;

use App\Core\MessageRecipient\Model\Person;
use Iterator;
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
     * @return Iterator<(int | string), array{string, (Ulid | null)}>
     */
    public static function constructorProvider(): Iterator
    {
        yield ['Dustin', null];
        yield ['Nilpferd', new Ulid()];
    }

    #[DataProvider('constructorProvider')]
    public function testConstructor(string $name, ?Ulid $id): void
    {
        $person = new Person($name, $id);

        if (!$id instanceof Ulid) {
            $person->getId()->toString();
        } else {
            self::assertSame($id, $person->getId());
        }
        self::assertSame($name, $person->getName());
        self::assertSame([], $person->getRoles());
    }
}
