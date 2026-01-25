<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Persistence\DoctrineORM\Type;

use App\Infrastructure\Persistence\DoctrineORM\Type\LocalTimeType;
use Brick\DateTime\LocalTime;
use DateTimeImmutable;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Iterator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(LocalTimeType::class)]
#[Small()]
final class LocalTimeTypeTest extends TestCase
{
    /**
     * @return Iterator<(int | string), array{(string | null), (LocalTime | null)}>
     */
    public static function convertToDatabaseValueProvider(): Iterator
    {
        yield [null, null];
        yield ['00:00', LocalTime::of(0, 0)];
        yield ['12:34:56', LocalTime::of(12, 34, 56)];
        yield ['23:59:59.999999999', LocalTime::of(23, 59, 59, 999999999)];
    }

    #[DataProvider('convertToDatabaseValueProvider')]
    public function testConvertToDatabaseValue(?string $expected, ?LocalTime $value): void
    {
        $platform = self::createStub(AbstractPlatform::class);
        $type = new LocalTimeType();

        $result = $type->convertToDatabaseValue($value, $platform);

        self::assertSame($expected, $result);
    }

    /**
     * @return Iterator<(int | string), array{mixed}>
     */
    public static function convertToDatabaseValueExceptionProvider(): Iterator
    {
        yield [true];
        yield [47.11];
        yield ['abcdef'];
        yield [new DateTimeImmutable()];
    }

    #[DataProvider('convertToDatabaseValueExceptionProvider')]
    public function testConvertToDatabaseValueException(mixed $value): void
    {
        $platform = self::createStub(AbstractPlatform::class);
        $type = new LocalTimeType();

        self::expectException(ConversionException::class);

        $type->convertToDatabaseValue($value, $platform);
    }

    /**
     * @return Iterator<(int | string), array{(LocalTime | null), (string | null)}>
     */
    public static function convertToPhpValueProvider(): Iterator
    {
        yield [null, null];
        yield [LocalTime::of(0, 0), '00:00'];
        yield [LocalTime::of(0, 0), '00:00:00'];
        yield [LocalTime::of(0, 0), '00:00:00.000'];
        yield [LocalTime::of(12, 34, 56), '12:34:56'];
        yield [LocalTime::of(12, 34, 56, 123456789), '12:34:56.123456789'];
        yield [LocalTime::of(23, 59, 59, 999999999), '23:59:59.999999999'];
    }

    #[DataProvider('convertToPhpValueProvider')]
    public function testConvertToPhpValue(?LocalTime $expected, ?string $value): void
    {
        $platform = self::createStub(AbstractPlatform::class);
        $type = new LocalTimeType();

        $result = $type->convertToPHPValue($value, $platform);

        if (!$expected instanceof LocalTime) {
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
        yield ['0'];
        yield ['0:00'];
        yield ['24:00'];
        yield ['abcdef'];
    }

    #[DataProvider('convertToPhpValueExceptionProvider')]
    public function testConvertToPhpValueException(mixed $value): void
    {
        $platform = self::createStub(AbstractPlatform::class);
        $type = new LocalTimeType();

        self::expectException(ConversionException::class);

        $type->convertToPHPValue($value, $platform);
    }

    public function testGetName(): void
    {
        $type = new LocalTimeType();

        self::assertSame(LocalTimeType::NAME, $type->getName());
    }

    /**
     * @return Iterator<(int | string), array{string, array<string, mixed>, AbstractPlatform}>
     */
    public static function getSqlDeclarationProvider(): Iterator
    {
        $mysql = new MySQLPlatform();

        yield ['VARCHAR(18)', [], $mysql];
    }

    /**
     * @param array<string, mixed> $column
     */
    #[DataProvider('getSqlDeclarationProvider')]
    public function testGetSqlDeclaration(string $expected, array $column, AbstractPlatform $platform): void
    {
        $type = new LocalTimeType();

        self::assertSame($expected, $type->getSQLDeclaration($column, $platform));
    }
}
