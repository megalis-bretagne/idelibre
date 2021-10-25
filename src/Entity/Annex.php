<?php

namespace App\Entity;

use App\Repository\AnnexRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=AnnexRepository::class)
 */
class Annex
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    #[Groups(['project:read'])]
    private $id;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank
     */
    #[Groups(['project:read'])]
    private $rank;

    /**
     * @ORM\OneToOne(targetEntity=File::class, cascade={"persist", "remove"}, inversedBy="annex")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull
     */
    #[Groups(['project:read'])]
    private $file;

    /**
     * @ORM\ManyToOne(targetEntity=Project::class, inversedBy="annexes")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Assert\NotNull
     */
    private $project;

    public function getId(): ?string
    {
        return $this->id;
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

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): self
    {
        $this->project = $project;

        return $this;
    }
}
