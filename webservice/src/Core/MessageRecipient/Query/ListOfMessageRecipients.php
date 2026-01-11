<?php

declare(strict_types=1);

namespace App\Core\MessageRecipient\Query;

use App\Core\Contracts\Bus\Query;
use App\Core\MessageRecipient\Model\Group;
use App\Core\MessageRecipient\Model\Person;
use App\Core\MessageRecipient\Model\Role;
use App\Core\MessageRecipient\ReadModel\RecipientListEntry;

/**
 * @implements Query<iterable<RecipientListEntry>>
 */
final readonly class ListOfMessageRecipients implements Query
{
    public const DEFAULT_PAGE_LENGTH = 25;

    public static function onlyGroups(?string $textFilter = null, ?int $page = null, ?int $perPage = null): self
    {
        return new self(Group::class, $textFilter, $page, $perPage);
    }

    public static function onlyPeople(?string $textFilter = null, ?int $page = null, ?int $perPage = null): self
    {
        return new self(Person::class, $textFilter, $page, $perPage);
    }

    public static function onlyRoles(?string $textFilter = null, ?int $page = null, ?int $perPage = null): self
    {
        return new self(Role::class, $textFilter, $page, $perPage);
    }

    public static function all(?string $textFilter = null, ?int $page = null, ?int $perPage = null): self
    {
        return new self(null, $textFilter, $page, $perPage);
    }

    public static function withoutFilter(?int $page = null, ?int $perPage = null): self
    {
        return new self(null, null, $page, $perPage);
    }

    public static function withTextSearch(string $textFilter, ?int $page = null, ?int $perPage = null): self
    {
        return new self(null, $textFilter, $page, $perPage);
    }

    /**
     * @param class-string|null $filterType
     */
    private function __construct(
        public ?string $filterType = null,
        public ?string $textFilter = null,
        public ?int $page = null,
        public ?int $perPage = null,
    ) {
    }
}
