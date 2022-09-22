<?php

namespace App\Entity;

use App\Repository\OtherdocRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use DateTimeImmutable;
use Doctrine\ORM\Mapping\OneToOne;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

#[ORM\Entity(repositoryClass: OtherdocRepository::class)]
class Otherdoc
{
    #[Id]
    #[GeneratedValue(strategy: 'UUID')]
    #[Column(type: 'guid')]
    #[Groups(['otherdoc:read'])]
    private $id;

    #[Column(type: 'string', length: 512)]
    #[Length(max: '512')]
    #[NotBlank]
    #[Groups(['otherdoc:read'])]
    private $name;

    #[Column(type: 'integer')]
    #[NotNull]
    #[Groups(['otherdoc:read'])]
    private $rank;

    #[OneToOne(inversedBy: 'otherdoc', targetEntity: File::class, cascade: ['persist', 'remove'])]
    #[JoinColumn(nullable: false)]
    #[NotNull]
    #[Groups(['otherdoc:read'])]
    private $file;

    #[Column(type: 'datetime_immutable')]
    private $createdAt;

    #[ManyToOne(targetEntity: Sitting::class, inversedBy: 'otherdocs')]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[NotNull]
    private ?Sitting $sitting = null;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
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
}
