<?php

declare(strict_types=1);

namespace App\Tests\Factory;

use App\Core\MessageRecipient\Model\Person;
use App\Core\MessageRecipient\Model\Role;
use Override;
use Symfony\Component\Uid\Ulid;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Role>
 */
final class RecipientRoleFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return Role::class;
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        $title = self::faker()->jobTitle();
        $area = self::faker()->city();

        return [
            'name' => $title.' '.$area,
            'person' => null,
            'id' => new Ulid(),
        ];
    }

    #[Override]
    protected function initialize(): static
    {
        return $this;
    }

    public function withName(string $name): static
    {
        return $this->with(['name' => $name]);
    }

    public function assignedTo(Person $person): static
    {
        return $this->with(['person' => $person]);
    }

    public function unassigned(): static
    {
        return $this->with(['person' => null]);
    }
}
