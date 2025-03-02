<?php

declare(strict_types=1);

namespace App\Core\MessageRecipient\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity]
#[ORM\Table(name: 'message_recipient')]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorMap([
    Group::DISCRIMINATOR => Group::class,
    Person::DISCRIMINATOR => Person::class,
    Role::DISCRIMINATOR => Role::class,
])]
abstract class AbstractMessageRecipient implements MessageRecipient
{
    #[ORM\Column(type: UlidType::NAME)]
    #[ORM\Id]
    public readonly Ulid $id;

    #[ORM\Column]
    private string $name;

    /**
     * @var Collection<int, Group>
     */
    #[ORM\ManyToMany(Group::class, mappedBy: 'members', )]
    private Collection $groups;

    public function __construct(string $name, ?Ulid $id = null)
    {
        $this->id = $id ?? new Ulid();
        $this->name = $name;
        $this->groups = new ArrayCollection();
    }

    /**
     * @return list<Group>
     */
    public function getGroups(): array
    {
        return $this->groups->getValues();
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
