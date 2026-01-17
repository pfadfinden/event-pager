<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\DoctrineORM\Type;

use Brick\DateTime\Instant;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Exception\InvalidFormat;
use Doctrine\DBAL\Types\Exception\InvalidType;
use Doctrine\DBAL\Types\Type;
use Override;
use function preg_match;
use function str_pad;

final class InstantType extends Type
{
    public const string NAME = 'instant';

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

        if (!$value instanceof Instant) {
            throw InvalidType::new($value, $this->getName(), ['null', Instant::class]);
        }

        if ($value->getEpochSecond() >= 10_000_000_000 || $value->getEpochSecond() <= -10_000_000_000) {
            throw InvalidFormat::new($value->toDecimal(), self::NAME, '-10M to 10M seconds around Unix epoch.');
        }

        return $value->toDecimal();
    }

    /**
     * @template T
     *
     * @param T $value
     */
    #[Override]
    public function convertToPHPValue($value, AbstractPlatform $platform): ?Instant
    {
        if (null === $value) {
            return null;
        }

        if (!is_numeric($value)) {
            throw InvalidType::new($value, $this->getName(), ['null', 'string', 'int', 'float']);
        }

        $matches = [];
        if (1 === preg_match('@\A(\d{1,10})(?:\.(\d{0,9}))?\z@', (string) $value, $matches)) {
            return Instant::of(
                (int) $matches[1],
                isset($matches[2]) ? (int) str_pad($matches[2], 9, '0') : 0
            );
        }

        throw InvalidFormat::new((string) $value, $this->getName(), '/\d{1,10}(\.\d{0,9})?/');
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getDecimalTypeDeclarationSQL([
            'precision' => 19,
            'scale' => 9,
        ]);
    }
}
