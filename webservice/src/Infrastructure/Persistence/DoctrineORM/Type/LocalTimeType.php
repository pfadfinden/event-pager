<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\DoctrineORM\Type;

use Brick\DateTime\LocalTime;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Exception\InvalidFormat;
use Doctrine\DBAL\Types\Exception\InvalidType;
use Doctrine\DBAL\Types\Type;
use Override;
use Throwable;
use function is_string;

final class LocalTimeType extends Type
{
    public const string NAME = 'local_time';

    /**
     * @template T
     *
     * @param T $value
     *
     * @return (T is null ? null : string)
     */
    #[Override]
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        if ($value instanceof LocalTime) {
            return $value->toISOString();
        }

        throw InvalidType::new($value, $this->getName(), ['null', LocalTime::class]);
    }

    /**
     * @template T
     *
     * @param T $value
     */
    #[Override]
    public function convertToPHPValue($value, AbstractPlatform $platform): ?LocalTime
    {
        if (null === $value) {
            return null;
        }

        if (!is_string($value)) {
            throw InvalidType::new($value, $this->getName(), ['null', 'string']);
        }

        try {
            return LocalTime::parse($value);
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
            'length' => 8 + 1 + 9,
        ]);
    }
}
