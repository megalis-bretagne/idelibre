<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Api\Controller\CloseSittingController;
use App\Api\Controller\SendSittingController;
use App\Repository\SittingRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=SittingRepository::class)
 * @ORM\Table(
 *     uniqueConstraints={@ORM\UniqueConstraint(
 *         name="IDX_SITTING_NAME_DATE_STRUCTURE",
 *         columns={"name", "structure_id", "date"}
 *     )})
 *
 * @UniqueEntity(
 *     fields={"type", "structure", "date"},
 *     errorPath="name",
 *     message="Une séance du même type existe déja à la même heure")
 */
#[ApiResource(
    collectionOperations: ['get'],
    itemOperations: [
        'get' => [
            'normalization_context' => ['groups' => ['sitting:item:get', 'sitting:read', 'theme:read']],
        ],
        'delete',
        'send' => [
            'method' => 'POST',
            'path' => '/sittings/{id}/sent',
            'controller' => SendSittingController::class,
            'status' => 200,
            'denormalization_context' => ['groups' => []],
        ],
        'close' => [
            'method' => 'POST',
            'path' => '/sittings/{id}/close',
            'controller' => CloseSittingController::class,
            'status' => 200,
            'denormalization_context' => ['groups' => []],
        ],
    ],
    shortName: 'sittings',
    attributes: ['order' => ['date' => 'DESC']],
    denormalizationContext: ['groups' => ['sitting:write']],
    normalizationContext: ['groups' => ['sitting:read']]
)]
class Sitting
{
    public const ARCHIVED = 'archived';
    public const ACTIVE = 'active';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     * @Groups({"sitting"})
     */
    #[Groups(['sitting:read'])]
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(max="255")
     * @Groups({"sitting"})
     */
    #[Groups(['sitting:read'])]
    private $name;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotNull(message="La date et l'heure sont obligatoires")
     * @Groups({"sitting"})
     */
    #[Groups(['sitting:read'])]
    private $date;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"sitting"})
     */
    #[Groups(['sitting:read'])]
    private $revision = 0;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"sitting"})
     */
    #[Groups(['sitting:read'])]
    private $isArchived = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max="255")
     * @Groups({"sitting"})
     */
    #[Groups(['sitting:read'])]
    private $place;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"sitting"})
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity=Convocation::class, mappedBy="sitting")
     */
    #[Groups(['sitting:item:get'])]
    private $convocations;

    /**
     * @ORM\ManyToOne(targetEntity=Type::class)
     * @ORM\JoinColumn(onDelete="SET NULL")
     * @Groups({"sitting"})
     */
    #[Groups(['sitting:read'])]
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity=Structure::class)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Assert\NotNull
     */
    private $structure;

    /**
     * @ORM\OneToOne(targetEntity=File::class, cascade={"persist", "remove"}, inversedBy="convocationSitting")
     */
    #[Groups(['sitting:item:get'])]
    private $convocationFile;

    /**
     * @ORM\OneToMany(targetEntity=Project::class, mappedBy="sitting")
     * @ORM\OrderBy({"rank" = "ASC"})
     */
    #[Groups(['sitting:item:get'])]
    private $projects;

    /**
     * @ORM\OneToOne(targetEntity=File::class, inversedBy="invitationSitting", cascade={"persist", "remove"})
     */
    #[Groups(['sitting:item:get'])]
    private $invitationFile;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"sitting"})
     */
    #[Groups(['sitting:read'])]
    private $comelusId;

    /**
     * @ORM\OneToOne(targetEntity=Reminder::class, mappedBy="sitting", cascade={"persist", "remove"})
     */
    #[Groups(['sitting:item:get'])]
    private $reminder;

    /**
     * @ORM\OneToMany(targetEntity=Timestamp::class, mappedBy="sitting")
     */
    #[Groups(['sitting:item:get'])]
    private $updatedTimestamps;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->convocations = new ArrayCollection();
        $this->projects = new ArrayCollection();
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
        $dateTime = new \DateTime(null, new \DateTimeZone($this->getStructure()->getTimezone()->getName()));
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
}
