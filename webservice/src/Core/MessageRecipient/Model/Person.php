<?php

declare(strict_types=1);

namespace App\Core\MessageRecipient\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity]
class Person extends AbstractMessageRecipient
{
    final public const DISCRIMINATOR = 'person';

    /**
     * @var Collection<int, Role>
     */
    #[ORM\OneToMany(Role::class, mappedBy: 'person')]
    private Collection $roles;

    public function __construct(
        string $name,
        ?Ulid $id = null,
    ) {
        parent::__construct($name, $id);

        $this->roles = new ArrayCollection();
    }

    /**
     * @return list<Role>
     */
    public function getRoles(): array
    {
        return $this->roles->getValues();
    }
}
