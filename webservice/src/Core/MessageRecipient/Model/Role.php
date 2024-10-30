<?php

declare(strict_types=1);

namespace App\Core\MessageRecipient\Model;

use Doctrine\ORM\Mapping as ORM;
use LogicException;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity]
class Role extends AbstractMessageRecipient implements Delegated
{
    final public const DISCRIMINATOR = 'role';

    public function __construct(
        string $name,
        #[ORM\ManyToOne(Person::class, inversedBy: 'roles')]
        public ?Person $person,
        ?Ulid $id = null,
    ) {
        parent::__construct($name, $id);
    }

    public function canResolve(): bool
    {
        return null !== $this->person;
    }

    public function resolve(): array
    {
        if (null === $this->person) {
            throw new LogicException();
        }

        return [$this->person];
    }
}
