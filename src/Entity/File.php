<?php

namespace App\Entity;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

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
     * @Assert\NotBlank
     * @Assert\Length(max="512")
     */
    private $path;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $size;

    /**
     * @ORM\Column(type="string", length=125)
     * @Assert\NotBlank
     * @Assert\Length(max="125")
     */
    private $name;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\OneToOne(targetEntity=Project::class, mappedBy="file")
     *
     * @var Project | null
     */
    private $project;

    /**
     * @ORM\OneToOne(targetEntity=Annex::class, mappedBy="file")
     *
     * @var Annex | null
     */
    private $annex;

    /**
     * @ORM\OneToOne(targetEntity=Sitting::class, mappedBy="convocationFile")
     *
     * @var Sitting | null
     */
    private $convocationSitting;

    /**
     * @ORM\OneToOne(targetEntity=Sitting::class, mappedBy="invitationFile", cascade={"persist", "remove"})
     */
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

        return $this->convocationSitting->getStructure();
    }

    public function getInvitationSitting(): ?Sitting
    {
        return $this->invitationSitting;
    }
}
