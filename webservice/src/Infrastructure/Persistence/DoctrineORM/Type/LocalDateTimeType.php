<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\DoctrineORM\Type;

use Brick\DateTime\LocalDateTime;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Exception\InvalidFormat;
use Doctrine\DBAL\Types\Exception\InvalidType;
use Doctrine\DBAL\Types\Type;
use Throwable;
use function is_string;

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

        throw InvalidType::new($value, $this->getName(), ['null', LocalDateTime::class]);
    }

    /**
     * @template T
     *
     * @param T $value
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?LocalDateTime
    {
        if (null === $value) {
            return null;
        }

        if (!is_string($value)) {
            throw InvalidType::new($value, $this->getName(), ['null', 'string']);
        }

        try {
            return LocalDateTime::parse($value);
        } catch (Throwable $ex) {
            throw InvalidFormat::new($value, $this->getName(), 'ISO 8601', $ex);
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
