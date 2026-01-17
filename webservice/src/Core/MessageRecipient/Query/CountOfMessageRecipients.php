<?php

declare(strict_types=1);

namespace App\Core\MessageRecipient\Query;

use App\Core\Contracts\Bus\Query;
use App\Core\MessageRecipient\Model\Group;
use App\Core\MessageRecipient\Model\Person;
use App\Core\MessageRecipient\Model\Role;

/**
 * @implements Query<int>
 */
final readonly class CountOfMessageRecipients implements Query
{
    public static function onlyGroups(?string $textFilter = null): self
    {
        return new self(Group::class, $textFilter);
    }

    public static function onlyPeople(?string $textFilter = null): self
    {
        return new self(Person::class, $textFilter);
    }

    public static function onlyRoles(?string $textFilter = null): self
    {
        return new self(Role::class, $textFilter);
    }

    public static function all(?string $textFilter = null): self
    {
        return new self(null, $textFilter);
    }

    public static function withoutFilter(): self
    {
        return new self();
    }

    public static function withTextSearch(string $textFilter): self
    {
        return new self(null, $textFilter);
    }

    /**
     * @param class-string|null $filterType
     */
    private function __construct(
        public ?string $filterType = null,
        public ?string $textFilter = null,
    ) {
    }
}
