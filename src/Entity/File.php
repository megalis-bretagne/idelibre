<?php

namespace App\Entity;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FileRepository")
 */
class File
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=512)
     */
    private $path;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $size;


    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\OneToOne(targetEntity=Project::class, mappedBy="file")
     * @var Project
     */
    private $project;


    /**
     * @ORM\OneToOne(targetEntity=Annex::class, mappedBy="file")
     * @var Annex
     */
    private $annex;


    /**
     * @ORM\OneToOne(targetEntity=Sitting::class, mappedBy="file")
     * @var Sitting
     */
    private $sitting;


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

        return $this->sitting->getStructure();
    }
}
