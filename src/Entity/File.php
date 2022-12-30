<?php

namespace App\Entity;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\OneToOne;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

#[Entity(repositoryClass: 'App\Repository\FileRepository')]
class File
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups(['sitting:detail', 'project:read'])]
    private $id;

    #[Column(type: 'string', length: 512)]
    #[NotBlank]
    #[Length(max: '512')]
    private $path;

    #[Column(type: 'float', nullable: true)]
    #[Groups(['sitting:detail', 'project:read'])]
    private $size;

    #[Column(type: 'string', length: 512)]
    #[NotBlank]
    #[Length(max: '125')]
    #[Groups(['sitting:detail', 'project:read'])]
    private $name;

    #[Column(type: 'datetime')]
    private $createdAt;

    /**
     * @var Project|null
     */
    #[OneToOne(mappedBy: 'file', targetEntity: Project::class, cascade: ['persist'])]
    private $project;

    /**
     * @var Annex|null
     */
    #[OneToOne(mappedBy: 'file', targetEntity: Annex::class)]
    private $annex;

    /**
     * @var Sitting|null
     */
    #[OneToOne(mappedBy: 'convocationFile', targetEntity: Sitting::class)]
    private $convocationSitting;

    #[OneToOne(mappedBy: 'invitationFile', targetEntity: Sitting::class, cascade: ['persist', 'remove'])]
    private $invitationSitting;

    /**
     * @var Otherdoc|null
     */
    #[OneToOne(mappedBy: 'file', targetEntity: Otherdoc::class)]
    private $otherdoc;

    #[Column(nullable: true)]
    private ?\DateTimeImmutable $cachedAt = null;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getSize(): ?float
    {
        return $this->size;
    }

    public function setSize(?float $size): self
    {
        $this->size = $size;

        return $this;
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

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getStructure(): Structure
    {
        if ($this->project) {
            return $this->project->getSitting()->getStructure();
        }
        if ($this->annex) {
            return $this->annex->getProject()->getSitting()->getStructure();
        }

        if ($this->convocationSitting) {
            return $this->convocationSitting->getStructure();
        }

        if ($this->otherdoc) {
            return $this->otherdoc->getSitting()->getStructure();
        }

        return $this->invitationSitting->getStructure();
    }

    public function getInvitationSitting(): ?Sitting
    {
        return $this->invitationSitting;
    }

    public function getCachedAt(): ?\DateTimeImmutable
    {
        return $this->cachedAt;
    }

    public function setCachedAt(?\DateTimeImmutable $cachedAt): self
    {
        $this->cachedAt = $cachedAt;

        return $this;
    }
}
