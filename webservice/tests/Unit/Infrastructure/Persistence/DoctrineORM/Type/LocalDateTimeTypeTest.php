<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Persistence\DoctrineORM\Type;

use App\Infrastructure\Persistence\DoctrineORM\Type\LocalDateTimeType;
use Brick\DateTime\LocalDateTime;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Iterator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(LocalDateTimeType::class)]
#[Small()]
final class LocalDateTimeTypeTest extends TestCase
{
    /**
     * @return Iterator<(int | string), array{(string | null), (LocalDateTime | null)}>
     */
    public static function convertToDatabaseValueProvider(): Iterator
    {
        yield [null, null];
        yield ['2024-11-30T00:00', LocalDateTime::of(2024, 11, 30)];
        yield ['2024-11-30T12:34:56.123456789', LocalDateTime::of(2024, 11, 30, 12, 34, 56, 123456789)];
        yield ['-123456-11-30T12:34:56.123456789', LocalDateTime::of(-123456, 11, 30, 12, 34, 56, 123456789)];
        yield ['-999999-01-01T00:00', LocalDateTime::min()];
        yield ['999999-12-31T23:59:59.999999999', LocalDateTime::max()];
    }

    #[DataProvider('convertToDatabaseValueProvider')]
    public function testConvertToDatabaseValue(?string $expected, ?LocalDateTime $value): void
    {
        $platform = self::createStub(AbstractPlatform::class);
        $type = new LocalDateTimeType();

        $result = $type->convertToDatabaseValue($value, $platform);

        self::assertSame($expected, $result);
    }

    /**
     * @return Iterator<(int | string), array{mixed}>
     */
    public static function convertToDatabaseValueExceptionProvider(): Iterator
    {
        yield [true];
        yield ['abcdef'];
    }

    #[DataProvider('convertToDatabaseValueExceptionProvider')]
    public function testConvertToDatabaseValueException(mixed $value): void
    {
        $platform = self::createStub(AbstractPlatform::class);
        $type = new LocalDateTimeType();

        self::expectException(ConversionException::class);

        $type->convertToDatabaseValue($value, $platform);
    }

    /**
     * @return Iterator<(int | string), array{(LocalDateTime | null), (string | null)}>
     */
    public static function convertToPhpValueProvider(): Iterator
    {
        yield [null, null];
        yield [LocalDateTime::of(2024, 11, 30), '2024-11-30T00:00'];
        yield [LocalDateTime::of(2024, 11, 30, 12, 34, 56, 123456789), '2024-11-30T12:34:56.123456789'];
        yield [LocalDateTime::of(-123456, 11, 30, 12, 34, 56, 123456789), '-123456-11-30T12:34:56.123456789'];
    }

    #[DataProvider('convertToPhpValueProvider')]
    public function testConvertToPhpValue(?LocalDateTime $expected, ?string $value): void
    {
        $platform = self::createStub(AbstractPlatform::class);
        $type = new LocalDateTimeType();

        $result = $type->convertToPHPValue($value, $platform);

        if (!$expected instanceof LocalDateTime) {
            self::assertNull($result);
        } else {
            self::assertNotNull($result);
            self::assertTrue($expected->isEqualTo($result));
        }
    }

    /**
     * @return Iterator<(int | string), array{mixed}>
     */
    public static function convertToPhpValueExceptionProvider(): Iterator
    {
        yield [''];
        yield ['.0'];
        yield ['abcdef'];
    }

    #[DataProvider('convertToPhpValueExceptionProvider')]
    public function testConvertToPhpValueException(mixed $value): void
    {
        $platform = self::createStub(AbstractPlatform::class);
        $type = new LocalDateTimeType();

        self::expectException(ConversionException::class);

        $type->convertToPHPValue($value, $platform);
    }

    public function testGetName(): void
    {
        $type = new LocalDateTimeType();

        self::assertSame(LocalDateTimeType::NAME, $type->getName());
    }

    /**
     * @return Iterator<(int | string), array{string, array<string, mixed>, AbstractPlatform}>
     */
    public static function getSqlDeclarationProvider(): Iterator
    {
        $mysql = new MySQLPlatform();

        yield ['VARCHAR(32)', [], $mysql];
    }

    /**
     * @param array<string, mixed> $column
     */
    #[DataProvider('getSqlDeclarationProvider')]
    public function testGetSqlDeclaration(string $expected, array $column, AbstractPlatform $platform): void
    {
        $type = new LocalDateTimeType();

        self::assertSame($expected, $type->getSQLDeclaration($column, $platform));
    }
}
