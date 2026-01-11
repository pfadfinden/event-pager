<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Persistence\DoctrineORM\Type;

use App\Infrastructure\Persistence\DoctrineORM\Type\UlidArrayType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;
use function count;

#[CoversClass(UlidArrayType::class)]
#[Small()]
final class UlidArrayTypeTest extends TestCase
{
    /**
     * @return array{0: ?Ulid[]}[]
     */
    public static function convertProvider(): array
    {
        return [
            [null],
            [[]],
            [[Ulid::fromString('01JEG92SG76GEXKAD368GC5YE3')]],
            [[Ulid::fromString('01JEG92SG76GEXKAD368GC5YE3'), Ulid::fromString('01JEG93Q56DDDFV1EC0KRFYKYP')]],
        ];
    }

    /** @param ?Ulid[] $input */
    #[DataProvider('convertProvider')]
    public function testConvert(?array $input): void
    {
        $platform = self::createStub(AbstractPlatform::class);
        $type = new UlidArrayType();

        $dbValue = $type->convertToDatabaseValue($input, $platform);
        $result = $type->convertToPHPValue($dbValue, $platform);

        if (null === $input) {
            self::assertCount(0, $result);

            return;
        }

        self::assertCount(count($input), $result);
        foreach ($result as $key => $r) {
            self::assertTrue($r->equals($input[$key]));
        }
    }

    public function testGetName(): void
    {
        $type = new UlidArrayType();

        self::assertSame(UlidArrayType::NAME, $type->getName());
    }

    /**
     * @return array{0: string, 1: array<string, mixed>, 2: AbstractPlatform}[]
     */
    public static function getSqlDeclarationProvider(): array
    {
        $mysql = new MySQLPlatform();

        return [
            ['LONGTEXT', [], $mysql],
        ];
    }

    /**
     * @param array<string, mixed> $column
     */
    #[DataProvider('getSqlDeclarationProvider')]
    public function testGetSqlDeclaration(string $expected, array $column, AbstractPlatform $platform): void
    {
        $type = new UlidArrayType();

        self::assertSame($expected, $type->getSQLDeclaration($column, $platform));
    }
}
