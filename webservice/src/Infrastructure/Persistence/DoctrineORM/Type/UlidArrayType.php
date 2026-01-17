<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\DoctrineORM\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Exception\ValueNotConvertible;
use Doctrine\DBAL\Types\Type;
use Override;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\AbstractUid;
use Symfony\Component\Uid\Ulid;
use function count;
use function is_array;
use function is_resource;
use function is_string;

final class UlidArrayType extends Type
{
    public const string NAME = 'ulid_array';

    private function ulidType(): UlidType
    {
        return new UlidType();
    }

    #[Override]
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (!is_array($value) || 0 === count($value)) {
            return null;
        }

        $ulidType = $this->ulidType();

        return implode(',', array_map(fn ($v): ?string => $ulidType->convertToDatabaseValue($v, $platform), $value));
    }

    /**
     * @return list<Ulid>
     */
    #[Override]
    public function convertToPHPValue($value, AbstractPlatform $platform): array
    {
        if (null === $value) {
            return [];
        }

        $value = is_resource($value) ? stream_get_contents($value) : $value;

        $ulidType = $this->ulidType();

        if (false === is_string($value)) {
            throw ValueNotConvertible::new($value, 'Ulid[]');
        }

        /* @phpstan-ignore return.type */
        return array_map(fn ($v): ?AbstractUid => $ulidType->convertToPHPValue($v, $platform), explode(',', $value));
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getClobTypeDeclarationSQL($column);
    }
}
