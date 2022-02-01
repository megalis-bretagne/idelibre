<?php

namespace App\Entity;

use App\Repository\AnnotationRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\CustomIdGenerator;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Ramsey\Uuid\Doctrine\UuidGenerator;

#[Entity(repositoryClass: AnnotationRepository::class)]
class Annotation
{
    #[Id]
    #[GeneratedValue(strategy: 'CUSTOM')]
    #[CustomIdGenerator(UuidGenerator::class)]
    #[Column(type: 'uuid', unique: true)]
    private $id;

    #[Column(type: 'integer', nullable: true)]
    private $page;

    #[Column(type: 'text', nullable: true)]
    private $text;

    #[Column(type: 'datetime', nullable: true)]
    private $createdAt;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private $author;

    #[ManyToOne(targetEntity: Project::class)]
    #[JoinColumn(onDelete: 'CASCADE')]
    private $project;

    #[ManyToOne(targetEntity: Annex::class)]
    #[JoinColumn(onDelete: 'CASCADE')]
    private $annex;

    #[ManyToOne(targetEntity: Sitting::class)]
    #[JoinColumn(onDelete: 'CASCADE')]
    private $sitting;

    #[Column(type: 'json', options: ['jsonb' => true])]
    private $rect;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getPage(): ?int
    {
        return $this->page;
    }

    public function setPage(?int $page): self
    {
        $this->page = $page;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

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

    public function getAnnex(): ?Annex
    {
        return $this->annex;
    }

    public function setAnnex(?Annex $annex): self
    {
        $this->annex = $annex;

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

    public function getRect(): ?string
    {
        return $this->rect;
    }

    public function setRect(?string $rect): self
    {
        $this->rect = $rect;

        return $this;
    }
}
