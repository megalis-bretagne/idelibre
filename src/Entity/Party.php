<?php

namespace App\Entity;

use App\Repository\PartyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=PartyRepository::class)
 * @ORM\Table(
 *     uniqueConstraints={@ORM\UniqueConstraint(
 *         name="IDX_PARTY_NAME_STRUCTURE",
 *         columns={"name", "structure_id"}
 *     )})
 * @UniqueEntity(
 *     fields={"name", "structure"},
 *     errorPath="name",
 *     message="Ce nom de groupe politique existe déjà")
 */
class Party
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    #[Groups(['party:read','user:detail'])]
    private $id;

    /**
     * @ORM\Column(type="integer")
     * On cree la sequence correspondante (party_legacy_seq) manuellement dans la migration Version20210430085944
     */
    private $legacyId;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(max="255")
     * @Assert\NotBlank
     */
    #[Groups(['party:read', 'party:write', 'user:detail'])]
    private $name;

    /**
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="party")
     */
    #[Groups(['party:detail'])]
    private $actors;

    /**
     * @ORM\ManyToOne(targetEntity=Structure::class)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Assert\NotNull
     */
    private $structure;

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
}
