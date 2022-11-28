<?php

namespace App\Entity;

use App\Repository\AnnexRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

#[Entity(repositoryClass: AnnexRepository::class)]
class Annex
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
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
