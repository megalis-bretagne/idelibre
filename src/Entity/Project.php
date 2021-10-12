<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ProjectRepository::class)
 */
class Project
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    #[Groups(["sitting:item:get"])]
    private $id;

    /**
     * @ORM\Column(type="string", length=512)
     * @Assert\Length(max="512")
     * @Assert\NotBlank
     */
    #[Groups(["sitting:item:get"])]
    private $name;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotNull
     */
    #[Groups(["sitting:item:get"])]
    private $rank;

    /**
     * @ORM\OneToOne(targetEntity=File::class, cascade={"persist", "remove"}, inversedBy="project")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull
     */
    private $file;

    /**
     * @ORM\ManyToOne(targetEntity=Theme::class)
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    #[Groups(["sitting:item:get"])]
    private $theme;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $reporter;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    #[Groups(["sitting:item:get"])]
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=Sitting::class, inversedBy="projects")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Assert\NotNull
     */
    private $sitting;

    /**
     * @ORM\OneToMany(targetEntity=Annex::class, mappedBy="project")
     * @ORM\OrderBy({"rank" = "ASC"})
     */
    #[Groups(["sitting:item:get"])]
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
