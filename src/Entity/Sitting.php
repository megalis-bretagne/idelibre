<?php

namespace App\Entity;

use App\Repository\SittingRepository;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\OrderBy;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotNull;

#[Entity(repositoryClass: SittingRepository::class)]
#[Table]
#[UniqueEntity(fields: ['type', 'structure', 'date'], message: 'Une séance du même type existe déja à la même heure', errorPath: 'name')]
#[UniqueConstraint(name: 'IDX_SITTING_NAME_DATE_STRUCTURE', columns: ['name', 'structure_id', 'date'])]
class Sitting
{
    public const ARCHIVED = 'archived';
    public const ACTIVE = 'active';

    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups(['sitting', 'sitting:read'])]
    private $id;

    #[Column(type: 'string', length: 255)]
    #[Length(max: '255')]
    #[Groups(groups: ['sitting', 'sitting:read'])]
    private $name;

    #[Column(type: 'datetime')]
    #[NotNull(message: "La date et l'heure sont obligatoires")]
    #[Groups(groups: ['sitting', 'sitting:read', 'sitting:write'])]
    private $date;

    #[Column(type: 'integer')]
    #[Groups(groups: ['sitting', 'sitting:detail'])]
    private $revision = 0;

    #[Column(type: 'boolean')]
    #[Groups(groups: ['sitting', 'sitting:read'])]
    private $isArchived = false;

    #[Column(type: 'string', length: 255, nullable: true)]
    #[Length(max: '255')]
    #[Groups(['sitting', 'sitting:detail', 'sitting:write'])]
    private $place;

    #[Column(type: 'datetime')]
    #[Groups(groups: ['sitting', 'sitting:detail'])]
    private $createdAt;

    #[OneToMany(mappedBy: 'sitting', targetEntity: Convocation::class)]
    private $convocations;

    #[ManyToOne(targetEntity: Type::class)]
    #[JoinColumn(onDelete: 'SET NULL')]
    #[Groups(['sitting', 'sitting:detail', 'sitting:write:post'])]
    private $type;

    #[ManyToOne(targetEntity: Structure::class)]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[NotNull]
    private $structure;

    #[OneToOne(inversedBy: 'convocationSitting', targetEntity: File::class, cascade: ['persist', 'remove'])]
    #[Groups(['sitting:detail'])]
    private $convocationFile;

    #[OneToMany(mappedBy: 'sitting', targetEntity: Project::class)]
    #[OrderBy(value: ['rank' => 'ASC'])]
    private $projects;

    #[OneToOne(inversedBy: 'invitationSitting', targetEntity: File::class, cascade: ['persist', 'remove'])]
    #[Groups(['sitting:detail'])]
    private $invitationFile;

    #[Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['sitting', 'sitting:detail'])]
    private $comelusId;

    #[OneToOne(mappedBy: 'sitting', targetEntity: Reminder::class, cascade: ['persist', 'remove'])]
    #[Groups(['sitting:detail'])]
    private $reminder;

    #[OneToMany(mappedBy: 'sitting', targetEntity: Timestamp::class)]
    private $updatedTimestamps;

    #[OneToMany(mappedBy: 'sitting', targetEntity: Otherdoc::class)]
    #[OrderBy(value: ['rank' => 'ASC'])]
    private $otherdocs;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->convocations = new ArrayCollection();
        $this->projects = new ArrayCollection();
        $this->otherdocs = new ArrayCollection();
        $this->updatedTimestamps = new ArrayCollection();
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

    public function getDate(): ?DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?DateTimeInterface $date): self
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

    public function getCreatedAt(): ?DateTimeInterface
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

    public function getConvocationFile(): ?File
    {
        return $this->convocationFile;
    }

    public function setConvocationFile(?File $convocationFile): self
    {
        $this->convocationFile = $convocationFile;

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

    public function getInvitationFile(): ?File
    {
        return $this->invitationFile;
    }

    public function setInvitationFile(?File $invitationFile): self
    {
        $this->invitationFile = $invitationFile;

        return $this;
    }

    public function getComelusId(): ?string
    {
        return $this->comelusId;
    }

    public function setComelusId(?string $comelusId): self
    {
        $this->comelusId = $comelusId;

        return $this;
    }

    public function getNameWithDate(): string
    {
        $dateTime = new DateTime('', new DateTimeZone($this->getStructure()->getTimezone()->getName()));
        $dateTime->setTimestamp($this->getDate()->getTimestamp());

        return $this->name . ' ' . $dateTime->format('d/m/y');
    }

    public function getReminder(): ?Reminder
    {
        return $this->reminder;
    }

    public function setReminder(Reminder $reminder): self
    {
        // set the owning side of the relation if necessary
        if ($reminder->getSitting() !== $this) {
            $reminder->setSitting($this);
        }

        $this->reminder = $reminder;

        return $this;
    }

    /**
     * @return Collection|Timestamp[]
     */
    public function getUpdatedTimestamps(): Collection
    {
        return $this->updatedTimestamps;
    }

    public function addUpdatedTimestamp(Timestamp $updatedTimestamp): self
    {
        if (!$this->updatedTimestamps->contains($updatedTimestamp)) {
            $this->updatedTimestamps[] = $updatedTimestamp;
            $updatedTimestamp->setSitting($this);
        }

        return $this;
    }

    public function removeUpdatedTimestamp(Timestamp $updatedTimestamp): self
    {
        if ($this->updatedTimestamps->removeElement($updatedTimestamp)) {
            // set the owning side to null (unless already changed)
            if ($updatedTimestamp->getSitting() === $this) {
                $updatedTimestamp->setSitting(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Otherdoc[]
     */
    public function getOtherdocs(): Collection
    {
        return $this->otherdocs;
    }

    public function addOtherdoc(Otherdoc $otherdoc): self
    {
        if (!$this->otherdocs->contains($otherdoc)) {
            $this->otherdocs[] = $otherdoc;
            $otherdoc->setSitting($this);
        }

        return $this;
    }

    public function removeOtherdoc(Otherdoc $otherdoc): self
    {
        if ($this->otherdocs->contains($otherdoc)) {
            $this->otherdocs->removeElement($otherdoc);
            // set the owning side to null (unless already changed)
            if ($otherdoc->getSitting() === $this) {
                $otherdoc->setSitting(null);
            }
        }

        return $this;
    }
}
