<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\UserRepository;
use App\Validator\OneAtMax;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="`user`")
 * @UniqueEntity("username", message="Ce nom d'utilisateur est déjà utilisé")
 */
#[ApiResource(
    collectionOperations: ['get', 'post'],
    itemOperations: [
        'get',
        'put',
    ],
    shortName: 'users',
    //denormalizationContext: ['groups' => [self::colPost]],
    normalizationContext: ['groups' => ['user:read']],
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     * @Groups({"user"})
     */
    #[Groups(['user:read', 'type:item:get', 'party:item:get', 'sitting:item:get'])]
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank()
     * @Assert\Length(max=255)
     * @OneAtMax()
     * @Groups({"user"})
     */
    #[Groups(['user:read', 'type:item:get', 'party:item:get', 'sitting:item:get'])]
    private $username;

    /**
     * @ORM\Column(type="string", length=180)
     * @Assert\NotBlank()
     * @Assert\Length(max=255)
     * @Assert\Email()
     * @Groups({"user"})
     */
    #[Groups(['user:read', 'type:item:get', 'party:item:get'])]
    private $email;

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(max=255)
     *
     * @Groups({"user"})
     */
    #[Groups(['user:read', 'type:item:get', 'party:item:get', 'sitting:item:get'])]
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(max=255)
     * @Groups({"user"})
     */
    #[Groups(['user:read', 'type:item:get', 'party:item:get', 'sitting:item:get'])]
    private $lastName;

    /**
     * @ORM\ManyToOne(targetEntity=Structure::class, inversedBy="users")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    #[Groups(['user:read'])]
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
    #[Groups(['user:read', 'sitting:item:get'])]
    private $role;

    /**
     * @ORM\ManyToMany(targetEntity=Type::class, mappedBy="associatedUsers")
     */
    #[Groups(['user:read'])]
    private $associatedTypes;

    /**
     * @ORM\ManyToOne(targetEntity=Party::class, inversedBy="actors")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    #[Groups(['user:read'])]
    private $party;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    #[Groups(['user:read'])]
    private $title;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    #[Groups(['user:read'])]
    private $gender;

    /**
     * @ORM\ManyToMany(targetEntity=Type::class, mappedBy="authorizedSecretaries")
     */
    private $authorizedTypes;

    /**
     * @ORM\Column(type="boolean")
     */
    #[Groups(['user:read'])]
    private $isActive = true;

    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    #[Groups(['user:read'])]
    private $phone;

    public function __construct()
    {
        $this->associatedTypes = new ArrayCollection();
        $this->authorizedTypes = new ArrayCollection();
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
        return (string) $this->username;
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
        return (string) $this->password;
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
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
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

    public function getParty(): ?Party
    {
        return $this->party;
    }

    public function setParty(?Party $party): self
    {
        $this->party = $party;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getGender(): ?int
    {
        return $this->gender;
    }

    public function setGender(?int $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * @return Collection|Type[]
     */
    public function getAuthorizedTypes(): Collection
    {
        return $this->authorizedTypes;
    }

    public function addAuthorizedType(Type $authorizedType): self
    {
        if (!$this->authorizedTypes->contains($authorizedType)) {
            $this->authorizedTypes[] = $authorizedType;
            $authorizedType->addAuthorizedSecretary($this);
        }

        return $this;
    }

    public function removeAuthorizedType(Type $authorizedType): self
    {
        if ($this->authorizedTypes->removeElement($authorizedType)) {
            $authorizedType->removeAuthorizedSecretary($this);
        }

        return $this;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }
}
