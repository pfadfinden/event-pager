<?php

declare(strict_types=1);

namespace App\Core\MessageRecipient\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use LogicException;
use Symfony\Component\Uid\Ulid;
use Traversable;

#[ORM\Entity]
class Group extends AbstractMessageRecipient implements Delegated
{
    final public const DISCRIMINATOR = 'group';

    /**
     * @var Collection<int, AbstractMessageRecipient>
     */
    #[ORM\ManyToMany(AbstractMessageRecipient::class, inversedBy: 'groups')]
    private Collection $members;

    public function __construct(string $name, ?Ulid $id = null)
    {
        parent::__construct($name, $id);

        $this->members = new ArrayCollection();
    }

    public function addMember(AbstractMessageRecipient $member): void
    {
        if ($this->getId()->equals($member->getId())) {
            throw new InvalidArgumentException('Cannot add group to itself: '.$this->getId());
        }
        $this->members->add($member);
    }

    public function removeMember(AbstractMessageRecipient $member): void
    {
        $this->members->removeElement($member);
    }

    /**
     * @return list<AbstractMessageRecipient>
     */
    public function getMembers(): array
    {
        return $this->members->getValues();
    }

    /**
     * Caution: yield from does not reset the keys. It preserves the keys returned by the Traversable object, or array.
     * Thus some values may share a common key with another yield or yield from, which, upon insertion into an array,
     * will overwrite former values with that key.
     *
     * See: https://www.php.net/manual/en/language.generators.syntax.php
     *
     * Solution: set preserve keys to false in array generation, example:
     * iterator_to_array($group1->getMembersRecursively(), false)
     *
     * @return Traversable<AbstractMessageRecipient>
     */
    public function getMembersRecursively(): Traversable
    {
        foreach ($this->members as $member) {
            if ($member instanceof self) {
                yield from $member->getMembersRecursively();
            } else {
                yield $member;
            }
        }
    }

    public function canResolve(): bool
    {
        return !$this->members->isEmpty();
    }

    public function resolve(): array
    {
        if ($this->members->isEmpty()) {
            throw new LogicException();
        }

        return $this->members->getValues();
    }
}
