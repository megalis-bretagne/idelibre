<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use DateTimeImmutable;
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
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

#[Entity(repositoryClass: ProjectRepository::class)]
class Project
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups(['project:read'])]
    private $id;

    #[Column(type: 'string', length: 512)]
    #[Length(max: '512')]
    #[NotBlank]
    #[Groups(['project:read'])]
    private $name;

    #[Column(type: 'integer')]
    #[NotNull]
    #[Groups(['project:read'])]
    private $rank;

    #[OneToOne(inversedBy: 'project', targetEntity: File::class, cascade: ['persist', 'remove'])]
    #[JoinColumn(nullable: false)]
    #[NotNull]
    #[Groups(['project:read'])]
    private $file;

    #[ManyToOne(targetEntity: Theme::class)]
    #[JoinColumn(onDelete: 'SET NULL')]
    #[Groups(['project:read'])]
    private $theme;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(onDelete: 'SET NULL')]
    #[Groups(['project:read'])]
    private $reporter;

    #[Column(type: 'datetime_immutable')]
    private $createdAt;

    #[ManyToOne(targetEntity: Sitting::class, inversedBy: 'projects')]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[NotNull]
    private $sitting;

    #[OneToMany(mappedBy: 'project', targetEntity: Annex::class)]
    #[OrderBy(value: ['rank' => 'ASC'])]
    #[Groups(['project:read'])]
    private $annexes;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->annexes = new ArrayCollection();
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

    public function getRank(): ?int
    {
        return $this->rank;
    }

    public function setRank(int $rank): self
    {
        $this->rank = $rank;

        return $this;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(File $file): self
    {
        $this->file = $file;

        return $this;
    }

    public function getTheme(): ?Theme
    {
        return $this->theme;
    }

    public function setTheme(?Theme $theme): self
    {
        $this->theme = $theme;

        return $this;
    }

    public function getReporter(): ?User
    {
        return $this->reporter;
    }

    public function setReporter(?User $reporter): self
    {
        $this->reporter = $reporter;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getSitting(): ?Sitting
    {
        return $this->sitting;
    }

    public function setSitting(?Sitting $sitting): self
    {
        $this->sitting = $sitting;

        return $this;
    }

    /**
     * @return Collection|Annex[]
     */
    public function getAnnexes(): Collection
    {
        return $this->annexes;
    }

    public function addAnnex(Annex $annex): self
    {
        if (!$this->annexes->contains($annex)) {
            $this->annexes[] = $annex;
            $annex->setProject($this);
        }

        return $this;
    }

    /**
     * @param Annex[] $annexes
     */
    public function addAnnexes(array $annexes): self
    {
        foreach ($annexes as $annex) {
            $this->addAnnex($annex);
        }

        return $this;
    }

    public function removeAnnex(Annex $annex): self
    {
        if ($this->annexes->contains($annex)) {
            $this->annexes->removeElement($annex);
            // set the owning side to null (unless already changed)
            if ($annex->getProject() === $this) {
                $annex->setProject(null);
            }
        }

        return $this;
    }
}
