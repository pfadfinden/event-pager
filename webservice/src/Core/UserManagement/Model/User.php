<?php

declare(strict_types=1);

namespace App\Core\UserManagement\Model;

use App\Infrastructure\Persistence\DoctrineORM\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use function assert;

#[ORM\Entity(UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type : Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 180, unique: true)]
    private string $username;

    #[ORM\Column(type: Types::STRING, length: 180, unique: false, nullable: true)]
    private ?string $displayname = null;

    /**
     * @var string[]
     */
    #[ORM\Column(type: Types::JSON)]
    private array $roles = [];

    /**
     * Password should not be null, but passwords can't be hashed unless the user is created.
     */
    #[ORM\Column(type: Types::STRING)]
    private string $password = '';

    #[ORM\Column(type: Types::STRING, length: 255, unique: true, nullable: true)]
    private ?string $externalId = null;

    public function __construct(string $username)
    {
        assert('' !== $username);

        $this->username = $username;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        assert('' !== $username);

        $this->username = $username;

        return $this;
    }

    public function getDisplayname(): ?string
    {
        return $this->displayname;
    }

    public function setDisplayname(?string $displayname): self
    {
        $this->displayname = $displayname;

        return $this;
    }

    /**
     * The public representation of the user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        assert('' !== $this->username);

        return $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        return array_values(array_unique($this->roles));
    }

    /**
     * @param string[] $roles
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function removeRole(string $role): self
    {
        foreach ($this->roles as $key => $value) {
            if ($value === $role) {
                array_splice($this->roles, $key, 1);
            }
        }

        return $this;
    }

    /**
     * @param string[] $roles
     */
    public function addRoles(array $roles): self
    {
        $this->roles = array_unique(array_merge($this->roles, $roles));

        return $this;
    }

    public function getExternalId(): ?string
    {
        return $this->externalId;
    }

    public function setExternalId(?string $externalId): self
    {
        $this->externalId = $externalId;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
    }
}
