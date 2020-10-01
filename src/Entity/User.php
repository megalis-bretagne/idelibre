<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="`user`")
 * @UniqueEntity("username")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $username;


    /**
     * @ORM\Column(type="string", length=180)
     */
    private $email;

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $lastName;

    /**
     * @ORM\ManyToOne(targetEntity=Structure::class, inversedBy="users")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $structure;

    /**
     * @ORM\ManyToOne(targetEntity=Group::class, inversedBy="users")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $group;

    /**
     * @ORM\ManyToOne(targetEntity=Role::class)
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $role;

    /**
     * @ORM\ManyToMany(targetEntity=Type::class, mappedBy="associatedUsers")
     */
    private $associatedTypes;


    public function __construct()
    {
        $this->associatedTypes = new ArrayCollection();
    }


    public function getId(): ?string
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string)$this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }




    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->getRole() ? $this->getRole()->getComposites() : [];
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_DEFAULT';
        return array_unique($roles);
    }


    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string)$this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
    }


    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->email,
            $this->roles
        ));
    }

    public function unserialize($serialized)
    {
        list(
            $this->id,
            $this->email,
            ) = unserialize($serialized, array("allowed_classes" => false));
    }

    public function getStructure(): ?Structure
    {
        return $this->structure;
    }

    public function setStructure(?Structure $structure): self
    {
        $this->structure = $structure;

        return $this;
    }

    public function getGroup(): ?Group
    {
        return $this->group;
    }

    public function setGroup(?Group $group): self
    {
        $this->group = $group;

        return $this;
    }

    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setRole(?Role $role): self
    {
        $this->role = $role;

        return $this;
    }

    /**
     * @return Collection|Type[]
     */
    public function getAssociatedTypes(): Collection
    {
        return $this->associatedTypes;
    }

    public function addAssociatedType(Type $associatedType): self
    {
        if (!$this->associatedTypes->contains($associatedType)) {
            $this->associatedTypes[] = $associatedType;
            $associatedType->addAssociatedUser($this);
        }

        return $this;
    }

    public function removeAssociatedType(Type $associatedType): self
    {
        if ($this->associatedTypes->contains($associatedType)) {
            $this->associatedTypes->removeElement($associatedType);
            $associatedType->removeAssociatedUser($this);
        }

        return $this;
    }
}
