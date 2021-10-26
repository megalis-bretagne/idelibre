<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Column;
use Symfony\Component\Validator\Constraints\NotBlank;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\JoinColumn;
use Symfony\Component\Validator\Constraints\NotNull;
use Doctrine\ORM\Mapping\ManyToOne;
use App\Repository\AnnexRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[Entity(repositoryClass: AnnexRepository::class)]
class Annex
{
    #[Id]
    #[GeneratedValue(strategy: 'UUID')]
    #[Column(type: 'guid')]
    #[Groups(['project:read'])]
    private $id;

    #[Column(type: 'integer')]
    #[NotBlank]
    #[Groups(['project:read'])]
    private $rank;

    #[OneToOne(inversedBy: 'annex', targetEntity: File::class, cascade: ['persist', 'remove'])]
    #[JoinColumn(nullable: false)]
    #[NotNull]
    #[Groups(['project:read'])]
    private $file;

    #[ManyToOne(targetEntity: Project::class, inversedBy: 'annexes')]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[NotNull]
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
