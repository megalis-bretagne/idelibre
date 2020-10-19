<?php

namespace App\Entity;

use App\Repository\AnnexRepository;
use Doctrine\ORM\Mapping as ORM;

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
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $rank;

    /**
     * @ORM\OneToOne(targetEntity=File::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $file;

    /**
     * @ORM\ManyToOne(targetEntity=Project::class, inversedBy="annexes")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
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
