<?php

namespace App\Entity;

use App\Repository\UserRepository;
use App\Validator\OneAtMax;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

#[Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity('username', message: "Ce nom d'utilisateur est déjà utilisé")]
#[Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups(['user', 'party:detail', 'user:read', 'type:detail', 'convocation:read'])]
    private $id;

    #[Column(type: 'string', length: 255, unique: true)]
    #[NotBlank]
    #[Length(max: 255)]
    #[OneAtMax]
    #[Groups(['user', 'party:detail', 'user:read', 'type:detail', 'user:write', 'convocation:read'])]
    private $username;

    #[Column(type: 'string', length: 180)]
    #[NotBlank]
    #[Length(max: 255)]
    #[Email]
    #[Groups(['user', 'party:detail', 'user:read', 'type:detail', 'user:write'])]
    private $email;

    #[Column(type: 'string')]
    private $password;

    #[Column(type: 'string', length: 255)]
    #[NotBlank]
    #[Length(max: 255)]
    #[Groups(['user', 'party:detail', 'user:read', 'type:detail', 'user:write', 'convocation:read'])]
    private $firstName;

    #[Column(type: 'string', length: 255)]
    #[NotBlank]
    #[Length(max: 255)]
    #[Groups(['user', 'party:detail', 'user:read', 'type:detail', 'user:write', 'convocation:read'])]
    private $lastName;

    #[ManyToOne(targetEntity: Structure::class, inversedBy: 'users')]
    #[JoinColumn(onDelete: 'CASCADE')]
    private $structure;

    #[ManyToOne(targetEntity: Group::class, inversedBy: 'users')]
    #[JoinColumn(onDelete: 'CASCADE')]
    private $group;

    #[ManyToOne(targetEntity: Role::class)]
    #[JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[NotNull]
    #[Groups(['user:read', 'user:write:post'])]
    private $role;

    #[ManyToMany(targetEntity: Type::class, mappedBy: 'associatedUsers')]
    private $associatedTypes;

    #[ManyToOne(targetEntity: Party::class, inversedBy: 'actors')]
    #[JoinColumn(onDelete: 'SET NULL')]
    #[Groups(['user', 'user:detail', 'user:write'])]
    private $party;

    #[Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['user:read', 'user:write'])]
    private $title;

    #[Column(type: 'integer', nullable: true)]
    #[Groups(['user:read', 'user:write'])]
    private $gender;

    #[ManyToMany(targetEntity: Type::class, mappedBy: 'authorizedSecretaries')]
    private $authorizedTypes;

    #[Column(type: 'boolean')]
    #[Groups(['user', 'user:read', 'user:write'])]
    private $isActive = true;

    #[Column(type: 'string', length: 30, nullable: true)]
    #[Groups(['user:read', 'user:write'])]
    private $phone;

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?Subscription $subscription = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $jwtInvalidBefore = null;

    #[Column(type: 'boolean', options: ['default' => false])]
    private bool $isDeputy = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mandatorType = null;

    #[ORM\OneToOne(targetEntity: self::class, cascade: ['persist', 'remove'])]
    private ?self $mandator = null;

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
    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->getRole() ? $this->getRole()->getComposites() : [];
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_DEFAULT';

        return array_unique($roles);
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        $this->setJwtInvalidBefore(new DateTimeImmutable());

        return $this;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
    }

    public function getUserIdentifier(): string
    {
        return $this->getUsername();
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

    public function getSubscription(): ?Subscription
    {
        return $this->subscription;
    }

    public function setSubscription(?Subscription $subscription): self
    {
        // unset the owning side of the relation if necessary
        if (null === $subscription && null !== $this->subscription) {
            $this->subscription->setUser(null);
        }

        // set the owning side of the relation if necessary
        if (null !== $subscription && $subscription->getUser() !== $this) {
            $subscription->setUser($this);
        }

        $this->subscription = $subscription;

        return $this;
    }

    public function getJwtInvalidBefore(): ?\DateTimeInterface
    {
        return $this->jwtInvalidBefore;
    }

    public function setJwtInvalidBefore(?\DateTimeInterface $jwtInvalidBefore): self
    {
        $this->jwtInvalidBefore = $jwtInvalidBefore;

        return $this;
    }

    public function isDeputy(): bool
    {
        return $this->isDeputy;
    }

    public function setIsDeputy(bool $isDeputy): self
    {
        $this->isDeputy = $isDeputy;

        return $this;
    }

    public function getMandatorType(): ?string
    {
        return $this->mandatorType;
    }

    public function setMandatorType(?string $mandatorType): self
    {
        $this->mandatorType = $mandatorType;

        return $this;
    }

    public function getMandator(): ?self
    {
        return $this->mandator;
    }

    public function setMandator(?self $mandator): self
    {
        $this->mandator = $mandator;

        return $this;
    }
}
