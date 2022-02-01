<?php

namespace App\Entity;

use App\Repository\TypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\CustomIdGenerator;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

#[Entity(repositoryClass: TypeRepository::class)]
#[Table]
#[UniqueEntity(fields: ['name', 'structure'], message: 'Ce type est déja utilisé dans cette structure', errorPath: 'name')]
#[UniqueConstraint(name: 'IDX_TYPE_NAME_STRUCTURE', columns: ['name', 'structure_id'])]
class Type
{
    #[Id]
    #[GeneratedValue(strategy: 'CUSTOM')]
    #[CustomIdGenerator(UuidGenerator::class)]
    #[Column(type: 'uuid', unique: true)]
    #[Groups(['sitting', 'type:read', 'sitting:detail'])]
    private $id;

    #[Column(type: 'string', length: 255, nullable: false)]
    #[NotBlank]
    #[Length(max: '255')]
    #[Groups(['sitting', 'type:read', 'type:write', 'sitting:detail'])]
    private $name;

    #[ManyToMany(targetEntity: User::class, inversedBy: 'associatedTypes')]
    #[Groups(['type:detail', 'type:write'])]
    private $associatedUsers;

    #[OneToOne(targetEntity: EmailTemplate::class, mappedBy: 'type')]
    private $emailTemplate;

    #[ManyToOne(targetEntity: Structure::class)]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[NotNull]
    private $structure;

    #[JoinTable(name: 'type_secretary')]
    #[ManyToMany(targetEntity: User::class, inversedBy: 'authorizedTypes')]
    private $authorizedSecretaries;

    #[Column(type: 'boolean', nullable: true)]
    #[Groups(['sitting', 'type:read', 'type:write'])]
    private $isSms;

    #[Column(type: 'boolean', nullable: true)]
    #[Groups(['sitting', 'type:read', 'type:write'])]
    private $isComelus;

    #[Groups(['type:detail', 'type:write'])]
    #[OneToOne(targetEntity: Reminder::class, mappedBy: 'type', cascade: ['persist', 'remove'])]
    private $reminder;

    public function __construct()
    {
        $this->associatedUsers = new ArrayCollection();
        $this->authorizedSecretaries = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getAssociatedUsers(): Collection
    {
        return $this->associatedUsers;
    }

    public function setAssociatedUsers(iterable $users): self
    {
        $this->associatedUsers = $users;

        return $this;
    }

    public function addAssociatedUser(User $associatedUser): self
    {
        if (!$this->associatedUsers->contains($associatedUser)) {
            $this->associatedUsers[] = $associatedUser;
        }

        return $this;
    }

    public function removeAssociatedUser(User $associatedUser): self
    {
        if ($this->associatedUsers->contains($associatedUser)) {
            $this->associatedUsers->removeElement($associatedUser);
        }

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

    /**
     * @return mixed
     */
    public function getEmailTemplate()
    {
        return $this->emailTemplate;
    }

    /**
     * @return Collection|User[]
     */
    public function getAuthorizedSecretaries(): Collection
    {
        return $this->authorizedSecretaries;
    }

    public function addAuthorizedSecretary(User $authorizedSecretary): self
    {
        if (!$this->authorizedSecretaries->contains($authorizedSecretary)) {
            $this->authorizedSecretaries[] = $authorizedSecretary;
        }

        return $this;
    }

    public function removeAuthorizedSecretary(User $authorizedSecretary): self
    {
        $this->authorizedSecretaries->removeElement($authorizedSecretary);

        return $this;
    }

    public function getIsSms(): bool
    {
        return $this->isSms ?? false;
    }

    public function setIsSms(bool $isSms): self
    {
        $this->isSms = $isSms;

        return $this;
    }

    public function getIsComelus(): bool
    {
        return $this->isComelus ?? false;
    }

    public function setIsComelus(?bool $isComelus): self
    {
        $this->isComelus = $isComelus;

        return $this;
    }

    public function getReminder(): ?Reminder
    {
        return $this->reminder;
    }

    public function setReminder(Reminder $reminder): self
    {
        // set the owning side of the relation if necessary
        if ($reminder->getType() !== $this) {
            $reminder->setType($this);
        }

        $this->reminder = $reminder;

        return $this;
    }
}
