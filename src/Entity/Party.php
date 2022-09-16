<?php

namespace App\Entity;

use App\Repository\PartyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

#[Entity(repositoryClass: PartyRepository::class)]
#[Table]
#[UniqueEntity(fields: ['name', 'structure'], message: 'Ce nom de groupe politique existe déjà', errorPath: 'name')]
#[UniqueConstraint(name: 'IDX_PARTY_NAME_STRUCTURE', columns: ['name', 'structure_id'])]
class Party
{
    #[Id]
    #[GeneratedValue(strategy: 'UUID')]
    #[Column(type: 'guid')]
    #[Groups(['party:read', 'user:detail'])]
    private $id;

    #[Column(type: 'integer')]
    private $legacyId;

    #[Column(type: 'string', length: 255)]
    #[Length(max: '255')]
    #[NotBlank]
    #[Groups(['party:read', 'party:write', 'user:detail'])]
    private $name;

    #[OneToMany(mappedBy: 'party', targetEntity: User::class)]
    #[Groups(['party:detail'])]
    private $actors;

    #[ManyToOne(targetEntity: Structure::class)]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[NotNull]
    private $structure;

    #[Column(type: 'string', length: 10, nullable: true)]
    #[Length(max: '10')]
    #[Groups(['party:read'])]
    private ?string $initials = null;

    public function __construct()
    {
        $this->actors = new ArrayCollection();
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
    public function getActors(): Collection
    {
        return $this->actors;
    }

    public function addActor(User $actor): self
    {
        if (!$this->actors->contains($actor)) {
            $this->actors[] = $actor;
            $actor->setParty($this);
        }

        return $this;
    }

    public function removeActor(User $actor): self
    {
        if ($this->actors->contains($actor)) {
            $this->actors->removeElement($actor);
            // set the owning side to null (unless already changed)
            if ($actor->getParty() === $this) {
                $actor->setParty(null);
            }
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

    public function getLegacyId(): ?int
    {
        return $this->legacyId;
    }

    /**
     * @return Party
     */
    public function setLegacyId(int $legacyId): self
    {
        $this->legacyId = $legacyId;

        return $this;
    }

    public function getInitials(): ?string
    {
        return $this->initials;
    }

    public function setInitials(?string $initials): self
    {
        $this->initials = $initials;

        return $this;
    }
}
