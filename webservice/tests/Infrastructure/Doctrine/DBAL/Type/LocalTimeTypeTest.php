<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Doctrine\DBAL\Type;

use App\Infrastructure\Persistence\DoctrineORM\Type\LocalTimeType;
use Brick\DateTime\LocalTime;
use DateTimeImmutable;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Types\ConversionException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(LocalTimeType::class)]
#[Small()]
final class LocalTimeTypeTest extends TestCase
{
    /**
     * @return array{0: ?string, 1: ?LocalTime}[]
     */
    public static function convertToDatabaseValueProvider(): array
    {
        return [
            [null, null],
            ['00:00', LocalTime::of(0, 0)],
            ['12:34:56', LocalTime::of(12, 34, 56)],
            ['23:59:59.999999999', LocalTime::of(23, 59, 59, 999999999)],
        ];
    }

    #[DataProvider('convertToDatabaseValueProvider')]
    public function testConvertToDatabaseValue(?string $expected, ?LocalTime $value): void
    {
        $platform = $this->createMock(AbstractPlatform::class);
        $type = new LocalTimeType();

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
            [47.11],
            ['abcdef'],
            [new DateTimeImmutable()],
        ];
    }

    #[DataProvider('convertToDatabaseValueExceptionProvider')]
    public function testConvertToDatabaseValueException(mixed $value): void
    {
        $platform = $this->createMock(AbstractPlatform::class);
        $type = new LocalTimeType();

        self::expectException(ConversionException::class);

        $type->convertToDatabaseValue($value, $platform);
    }

    /**
     * @return array{0: ?LocalTime, 1: ?string}[]
     */
    public static function convertToPhpValueProvider(): array
    {
        return [
            [null, null],
            [LocalTime::of(0, 0), '00:00'],
            [LocalTime::of(0, 0), '00:00:00'],
            [LocalTime::of(0, 0), '00:00:00.000'],
            [LocalTime::of(12, 34, 56), '12:34:56'],
            [LocalTime::of(12, 34, 56, 123456789), '12:34:56.123456789'],
            [LocalTime::of(23, 59, 59, 999999999), '23:59:59.999999999'],
        ];
    }

    #[DataProvider('convertToPhpValueProvider')]
    public function testConvertToPhpValue(?LocalTime $expected, ?string $value): void
    {
        $platform = $this->createMock(AbstractPlatform::class);
        $type = new LocalTimeType();

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
            ['0'],
            ['0:00'],
            ['24:00'],
            ['abcdef'],
        ];
    }

    #[DataProvider('convertToPhpValueExceptionProvider')]
    public function testConvertToPhpValueException(mixed $value): void
    {
        $platform = $this->createMock(AbstractPlatform::class);
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
     * @return array{0: string, 1: array<string, mixed>, 2: AbstractPlatform}[]
     */
    public static function getSqlDeclarationProvider(): array
    {
        $mysql = new MySQLPlatform();

        return [
            ['VARCHAR(18)', [], $mysql],
        ];
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
