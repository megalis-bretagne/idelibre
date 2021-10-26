<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Column;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Doctrine\ORM\Mapping\OneToOne;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[Entity(repositoryClass: 'App\Repository\FileRepository')]
class File
{
    #[Id]
    #[GeneratedValue(strategy: 'UUID')]
    #[Column(type: 'guid')]
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
    #[OneToOne(mappedBy: 'file', targetEntity: Project::class)]
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

        return $this->invitationSitting->getStructure();
    }
    public function getInvitationSitting(): ?Sitting
    {
        return $this->invitationSitting;
    }
}
