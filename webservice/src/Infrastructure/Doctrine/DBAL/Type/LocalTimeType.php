<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\DBAL\Type;

use Brick\DateTime\LocalTime;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use Throwable;
use function is_string;

final class LocalTimeType extends Type
{
    public const NAME = 'local_time';

    /**
     * @template T
     *
     * @param T $value
     *
     * @return (T is null ? null : string)
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        if ($value instanceof LocalTime) {
            return $value->toISOString();
        }

        throw ConversionException::conversionFailedInvalidType($value, $this->getName(), ['null', LocalTime::class]);
    }

    /**
     * @template T
     *
     * @param T $value
     *
     * @return (T is null ? null : LocalTime)
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?LocalTime
    {
        if (null === $value) {
            return null;
        }

        if (!is_string($value)) {
            throw ConversionException::conversionFailedInvalidType($value, $this->getName(), ['null', 'string']);
        }

        try {
            return LocalTime::parse($value);
        } catch (Throwable $ex) {
            throw ConversionException::conversionFailedFormat($value, $this->getName(), 'ISO 8601', $ex);
        }
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL([
            'length' => 8 + 1 + 9,
        ]);
    }
}
