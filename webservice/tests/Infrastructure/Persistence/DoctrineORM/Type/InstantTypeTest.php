<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Persistence\DoctrineORM\Type;

use App\Infrastructure\Persistence\DoctrineORM\Type\InstantType;
use Brick\DateTime\Instant;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Types\ConversionException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(InstantType::class)]
#[Small()]
final class InstantTypeTest extends TestCase
{
    /**
     * @return array{0: ?string, 1: ?Instant}[]
     */
    public static function convertToDatabaseValueProvider(): array
    {
        return [
            [null, null],
            ['0', Instant::epoch()],
            ['1234567890.123456789', Instant::of(1234567890, 123456789)],
            ['-123456789.123456789', Instant::of(-123456789, 123456789)],
            ['9999999999.999999999', Instant::of(9_999_999_999, 999_999_999)],
            ['-9999999999.999999999', Instant::of(-9_999_999_999, 999_999_999)],
        ];
    }

    #[DataProvider('convertToDatabaseValueProvider')]
    public function testConvertToDatabaseValue(?string $expected, ?Instant $value): void
    {
        $platform = self::createStub(AbstractPlatform::class);
        $type = new InstantType();

        $result = $type->convertToDatabaseValue($value, $platform);

        self::assertSame($expected, $result);
    }

    /**
     * @return array{0: mixed}[]
     */
    public static function convertToDatabaseValueExceptionProvider(): array
    {
        return [
            [true],
            [Instant::min()],
            [Instant::of(-10_000_000_000, 0)],
            [Instant::of(10_000_000_000, 0)],
            [Instant::max()],
        ];
    }

    #[DataProvider('convertToDatabaseValueExceptionProvider')]
    public function testConvertToDatabaseValueException(mixed $value): void
    {
        $platform = self::createStub(AbstractPlatform::class);
        $type = new InstantType();

        self::expectException(ConversionException::class);

        $type->convertToDatabaseValue($value, $platform);
    }

    /**
     * @return array{0: ?Instant, 1: mixed}[]
     */
    public static function convertToPhpValueProvider(): array
    {
        return [
            [null, null],
            [Instant::epoch(), 0],
            [Instant::epoch(), 0.0],
            [Instant::epoch(), '0'],
            [Instant::epoch(), '0.'],
            [Instant::epoch(), '0.0'],
            [Instant::of(42, 125000000), 42.125],
            [Instant::of(4711, 250000000), 4711.25],
            [Instant::of(0, 123456789), '0.123456789'],
            [Instant::of(123456789, 0), '123456789.0'],
            [Instant::of(123456, 789000000), '123456.789'],
        ];
    }

    #[DataProvider('convertToPhpValueProvider')]
    public function testConvertToPhpValue(?Instant $expected, mixed $value): void
    {
        $platform = self::createStub(AbstractPlatform::class);
        $type = new InstantType();

        $result = $type->convertToPHPValue($value, $platform);

        if (null === $expected) {
            self::assertNull($result);
        } else {
            self::assertNotNull($result);
            self::assertTrue($expected->isEqualTo($result));
        }
    }

    /**
     * @return array{0: mixed}[]
     */
    public static function convertToPhpValueExceptionProvider(): array
    {
        return [
            [''],
            ['.0'],
            ['abcdef'],
        ];
    }

    #[DataProvider('convertToPhpValueExceptionProvider')]
    public function testConvertToPhpValueException(mixed $value): void
    {
        $platform = self::createStub(AbstractPlatform::class);
        $type = new InstantType();

        self::expectException(ConversionException::class);

        $type->convertToPHPValue($value, $platform);
    }

    public function testGetName(): void
    {
        $type = new InstantType();

        self::assertSame(InstantType::NAME, $type->getName());
    }

    /**
     * @return array{0: string, 1: array<string, mixed>, 2: AbstractPlatform}[]
     */
    public static function getSqlDeclarationProvider(): array
    {
        $mysql = new MySQLPlatform();

        return [
            ['NUMERIC(19, 9)', [], $mysql],
        ];
    }

    /**
     * @param array<string, mixed> $column
     */
    #[DataProvider('getSqlDeclarationProvider')]
    public function testGetSqlDeclaration(string $expected, array $column, AbstractPlatform $platform): void
    {
        $type = new InstantType();

        self::assertSame($expected, $type->getSQLDeclaration($column, $platform));
    }
}
