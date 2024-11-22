<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\DBAL\Type;

use Brick\DateTime\LocalDateTime;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use Throwable;

final class LocalDateTimeType extends Type
{
    public const NAME = 'local_datetime';

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

        if ($value instanceof LocalDateTime) {
            return $value->toISOString();
        }

        throw ConversionException::conversionFailedInvalidType($value, $this->getName(), ['null', LocalDateTime::class]);
    }

    /**
     * @template T
     *
     * @param T $value
     *
     * @return (T is null ? null : LocalDateTime)
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?LocalDateTime
    {
        if (null === $value) {
            return null;
        }

        try {
            return LocalDateTime::parse((string) $value);
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
            'length' => 1 + (6 + 1 + 2 + 1 + 2) + 1 + 8 + 1 + 9,
        ]);
    }
}
