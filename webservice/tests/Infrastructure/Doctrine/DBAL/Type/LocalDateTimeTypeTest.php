<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Doctrine\DBAL\Type;

use App\Infrastructure\Doctrine\DBAL\Type\LocalDateTimeType;
use Brick\DateTime\LocalDateTime;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Types\ConversionException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(LocalDateTimeType::class)]
#[Small()]
final class LocalDateTimeTypeTest extends TestCase
{
    /**
     * @return array{0: ?string, 1: ?LocalDateTime}[]
     */
    public static function convertToDatabaseValueProvider(): array
    {
        return [
            [null, null],
            ['2024-11-30T00:00', LocalDateTime::of(2024, 11, 30)],
            ['2024-11-30T12:34:56.123456789', LocalDateTime::of(2024, 11, 30, 12, 34, 56, 123456789)],
            ['-123456-11-30T12:34:56.123456789', LocalDateTime::of(-123456, 11, 30, 12, 34, 56, 123456789)],
            ['-999999-01-01T00:00', LocalDateTime::min()],
            ['999999-12-31T23:59:59.999999999', LocalDateTime::max()],
        ];
    }

    #[DataProvider('convertToDatabaseValueProvider')]
    public function testConvertToDatabaseValue(?string $expected, ?LocalDateTime $value): void
    {
        $platform = $this->createMock(AbstractPlatform::class);
        $type = new LocalDateTimeType();

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
            ['abcdef'],
        ];
    }

    #[DataProvider('convertToDatabaseValueExceptionProvider')]
    public function testConvertToDatabaseValueException(mixed $value): void
    {
        $platform = $this->createMock(AbstractPlatform::class);
        $type = new LocalDateTimeType();

        self::expectException(ConversionException::class);

        $type->convertToDatabaseValue($value, $platform);
    }

    /**
     * @return array{0: ?LocalDateTime, 1: ?string}[]
     */
    public static function convertToPhpValueProvider(): array
    {
        return [
            [null, null],
            [LocalDateTime::of(2024, 11, 30), '2024-11-30T00:00'],
            [LocalDateTime::of(2024, 11, 30, 12, 34, 56, 123456789), '2024-11-30T12:34:56.123456789'],
            [LocalDateTime::of(-123456, 11, 30, 12, 34, 56, 123456789), '-123456-11-30T12:34:56.123456789'],
        ];
    }

    #[DataProvider('convertToPhpValueProvider')]
    public function testConvertToPhpValue(?LocalDateTime $expected, ?string $value): void
    {
        $platform = $this->createMock(AbstractPlatform::class);
        $type = new LocalDateTimeType();

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
        $platform = $this->createMock(AbstractPlatform::class);
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
     * @return array{0: string, 1: array<string, mixed>, 2: AbstractPlatform}[]
     */
    public static function getSqlDeclarationProvider(): array
    {
        $mysql = new MySQLPlatform();

        return [
            ['VARCHAR(32)', [], $mysql],
        ];
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
