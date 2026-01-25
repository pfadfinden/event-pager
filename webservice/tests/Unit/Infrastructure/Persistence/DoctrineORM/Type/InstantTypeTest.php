<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Persistence\DoctrineORM\Type;

use App\Infrastructure\Persistence\DoctrineORM\Type\InstantType;
use Brick\DateTime\Instant;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Iterator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(InstantType::class)]
#[Small()]
final class InstantTypeTest extends TestCase
{
    /**
     * @return Iterator<(int | string), array{(string | null), (Instant | null)}>
     */
    public static function convertToDatabaseValueProvider(): Iterator
    {
        yield [null, null];
        yield ['0', Instant::epoch()];
        yield ['1234567890.123456789', Instant::of(1234567890, 123456789)];
        yield ['-123456789.123456789', Instant::of(-123456789, 123456789)];
        yield ['9999999999.999999999', Instant::of(9_999_999_999, 999_999_999)];
        yield ['-9999999999.999999999', Instant::of(-9_999_999_999, 999_999_999)];
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
     * @return Iterator<(int | string), array{mixed}>
     */
    public static function convertToDatabaseValueExceptionProvider(): Iterator
    {
        yield [true];
        yield [Instant::min()];
        yield [Instant::of(-10_000_000_000, 0)];
        yield [Instant::of(10_000_000_000, 0)];
        yield [Instant::max()];
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
     * @return Iterator<(int | string), array{(Instant | null), mixed}>
     */
    public static function convertToPhpValueProvider(): Iterator
    {
        yield [null, null];
        yield [Instant::epoch(), 0];
        yield [Instant::epoch(), 0.0];
        yield [Instant::epoch(), '0'];
        yield [Instant::epoch(), '0.'];
        yield [Instant::epoch(), '0.0'];
        yield [Instant::of(42, 125000000), 42.125];
        yield [Instant::of(4711, 250000000), 4711.25];
        yield [Instant::of(0, 123456789), '0.123456789'];
        yield [Instant::of(123456789, 0), '123456789.0'];
        yield [Instant::of(123456, 789000000), '123456.789'];
    }

    #[DataProvider('convertToPhpValueProvider')]
    public function testConvertToPhpValue(?Instant $expected, mixed $value): void
    {
        $platform = self::createStub(AbstractPlatform::class);
        $type = new InstantType();

        $result = $type->convertToPHPValue($value, $platform);

        if (!$expected instanceof Instant) {
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
     * @return Iterator<(int | string), array{string, array<string, mixed>, AbstractPlatform}>
     */
    public static function getSqlDeclarationProvider(): Iterator
    {
        $mysql = new MySQLPlatform();

        yield ['NUMERIC(19, 9)', [], $mysql];
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
