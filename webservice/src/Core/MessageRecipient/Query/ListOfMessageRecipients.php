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
    public static function onlyGroups(): self
    {
        return new self(Group::class);
    }

    public static function onlyPeople(): self
    {
        return new self(Person::class);
    }

    public static function onlyRoles(): self
    {
        return new self(Role::class);
    }

    public static function all(): self
    {
        return new self(null);
    }

    /**
     * @param class-string|null $filterType
     */
    private function __construct(public ?string $filterType = null)
    {
    }
}
