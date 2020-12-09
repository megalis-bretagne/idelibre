<?php

namespace App\Entity;

use App\Repository\SittingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=SittingRepository::class)
 * @ORM\Table(
 *     uniqueConstraints={@ORM\UniqueConstraint(
 *         name="IDX_SITTING_NAME_DATE_STRUCTURE",
 *         columns={"name", "structure_id", "date"}
 *     )})
 *
 * @UniqueEntity(
 *     fields={"name", "structure", "date"},
 *     errorPath="name",
 *     message="Une séance du même type existe déja à la même heure")
 */
class Sitting
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="integer")
     */
    private $revision = 0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isArchived = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $place;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity=Convocation::class, mappedBy="sitting")
     */
    private $convocations;

    /**
     * @ORM\ManyToOne(targetEntity=Type::class)
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity=Structure::class)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $structure;

    /**
     * @ORM\OneToOne(targetEntity=File::class, cascade={"persist", "remove"}, inversedBy="sitting")
     */
    private $file;

    /**
     * @ORM\OneToMany(targetEntity=Project::class, mappedBy="sitting")
     */
    private $projects;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->convocations = new ArrayCollection();
        $this->projects = new ArrayCollection();
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

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getRevision(): ?int
    {
        return $this->revision;
    }

    public function setRevision(int $revision): self
    {
        $this->revision = $revision;

        return $this;
    }

    public function getIsArchived(): ?bool
    {
        return $this->isArchived;
    }

    public function setIsArchived(bool $isArchived): self
    {
        $this->isArchived = $isArchived;

        return $this;
    }

    public function getPlace(): ?string
    {
        return $this->place;
    }

    public function setPlace(?string $place): self
    {
        $this->place = $place;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @return Collection|Convocation[]
     */
    public function getConvocations(): Collection
    {
        return $this->convocations;
    }

    public function addConvocation(Convocation $convocation): self
    {
        if (!$this->convocations->contains($convocation)) {
            $this->convocations[] = $convocation;
            $convocation->setSitting($this);
        }

        return $this;
    }

    public function removeConvocation(Convocation $convocation): self
    {
        if ($this->convocations->contains($convocation)) {
            $this->convocations->removeElement($convocation);
            // set the owning side to null (unless already changed)
            if ($convocation->getSitting() === $this) {
                $convocation->setSitting(null);
            }
        }

        return $this;
    }

    public function getType(): ?Type
    {
        return $this->type;
    }

    public function setType(?Type $type): self
    {
        $this->type = $type;

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

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file): self
    {
        $this->file = $file;

        return $this;
    }

    /**
     * @return Collection|Project[]
     */
    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function addProject(Project $project): self
    {
        if (!$this->projects->contains($project)) {
            $this->projects[] = $project;
            $project->setSitting($this);
        }

        return $this;
    }

    public function removeProject(Project $project): self
    {
        if ($this->projects->contains($project)) {
            $this->projects->removeElement($project);
            // set the owning side to null (unless already changed)
            if ($project->getSitting() === $this) {
                $project->setSitting(null);
            }
        }

        return $this;
    }
}
