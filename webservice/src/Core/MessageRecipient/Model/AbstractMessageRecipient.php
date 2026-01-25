<?php

declare(strict_types=1);

namespace App\Core\MessageRecipient\Model;

use App\Core\TransportContract\Port\Transport;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
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
abstract class AbstractMessageRecipient implements MessageRecipient, Stringable
{
    #[ORM\Column(type: UlidType::NAME)]
    #[ORM\Id]
    private Ulid $id;

    #[ORM\Column]
    private string $name;

    /**
     * @var Collection<int, Group>
     */
    #[ORM\ManyToMany(Group::class, mappedBy: 'members', )]
    private Collection $groups;

    /**
     * @var Collection<int, RecipientTransportConfiguration>
     */
    #[ORM\OneToMany(RecipientTransportConfiguration::class, mappedBy: 'recipient', cascade: ['all'], orphanRemoval: true)]
    private Collection $transportConfiguration;

    public function __construct(string $name, ?Ulid $id = null)
    {
        $this->id = $id ?? new Ulid();
        $this->name = $name;
        $this->groups = new ArrayCollection();
        $this->transportConfiguration = new ArrayCollection();
    }

    public function getId(): Ulid
    {
        return $this->id;
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

    public function hasTransportConfigurations(): bool
    {
        return !$this->transportConfiguration->isEmpty();
    }

    /**
     * Returns the vendor-specific config for the first enabled configuration matching the transport key.
     * Note: With multiple configs per transport, this returns the highest-ranked enabled config.
     *
     * @phpstan-ignore missingType.iterableValue (JSON compatible array)
     */
    public function getTransportConfigurationFor(Transport $transport): ?array
    {
        $transportKey = $transport->key();
        foreach ($this->getTransportConfiguration() as $config) {
            if ($config->getKey() === $transportKey && $config->isEnabled) {
                return $config->getVendorSpecificConfig();
            }
        }

        return null;
    }

    public function __toString(): string
    {
        return $this->getName();
    }

    /**
     * Returns all transport configurations sorted by rank descending (highest rank first).
     *
     * @return list<RecipientTransportConfiguration>
     */
    public function getTransportConfiguration(): array
    {
        $configs = $this->transportConfiguration->toArray();
        usort($configs, static fn (RecipientTransportConfiguration $a, RecipientTransportConfiguration $b): int => $b->getRank() <=> $a->getRank());

        return $configs;
    }

    /**
     * Adds a new transport configuration with automatic rank assignment.
     * Multiple configurations with the same key are allowed.
     */
    public function addTransportConfiguration(string $key): RecipientTransportConfiguration
    {
        return $this->addTransportConfigurationWithId($key, null);
    }

    /**
     * Adds a transport configuration with a specific ID (for import scenarios).
     * Multiple configurations with the same key are allowed.
     */
    public function addTransportConfigurationWithId(string $key, ?Ulid $id): RecipientTransportConfiguration
    {
        // Calculate the next rank (highest existing rank + 1)
        $maxRank = 0;
        foreach ($this->transportConfiguration as $config) {
            if ($config->getRank() > $maxRank) {
                $maxRank = $config->getRank();
            }
        }

        $config = new RecipientTransportConfiguration($this, $key, $id);
        $config->setRank($maxRank + 1);
        $this->transportConfiguration->add($config);

        return $config;
    }

    public function getTransportConfigurationById(string|Ulid $id): ?RecipientTransportConfiguration
    {
        foreach ($this->transportConfiguration as $config) {
            if ($config->getId()->toString() === (string) $id) {
                return $config;
            }
        }

        return null;
    }

    /**
     * Returns the first transport configuration matching the given key.
     * Useful for backward-compatible import/export operations.
     */
    public function getFirstTransportConfigurationByKey(string $key): ?RecipientTransportConfiguration
    {
        foreach ($this->getTransportConfiguration() as $config) {
            if ($config->getKey() === $key) {
                return $config;
            }
        }

        return null;
    }

    public function removeTransportConfigurationById(string $id): void
    {
        foreach ($this->transportConfiguration as $key => $config) {
            if ($config->getId()->toString() === $id) {
                $this->transportConfiguration->remove($key);

                return;
            }
        }
    }
}
