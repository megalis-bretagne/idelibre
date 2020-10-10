<?php

namespace App\Entity;

use App\Repository\SittingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SittingRepository::class)
 */
class Sitting
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="integer")
     */
    private $revision;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isArchived;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $place;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity=Convocation::class, mappedBy="sitting")
     */
    private $convocations;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->convocations = new ArrayCollection();
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

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getRevision(): ?int
    {
        return $this->revision;
    }

    public function setRevision(int $revision): self
    {
        $this->revision = $revision;

        return $this;
    }

    public function getIsArchived(): ?bool
    {
        return $this->isArchived;
    }

    public function setIsArchived(bool $isArchived): self
    {
        $this->isArchived = $isArchived;

        return $this;
    }

    public function getPlace(): ?string
    {
        return $this->place;
    }

    public function setPlace(?string $place): self
    {
        $this->place = $place;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @return Collection|Convocation[]
     */
    public function getConvocations(): Collection
    {
        return $this->convocations;
    }

    public function addConvocation(Convocation $convocation): self
    {
        if (!$this->convocations->contains($convocation)) {
            $this->convocations[] = $convocation;
            $convocation->setSitting($this);
        }

        return $this;
    }

    public function removeConvocation(Convocation $convocation): self
    {
        if ($this->convocations->contains($convocation)) {
            $this->convocations->removeElement($convocation);
            // set the owning side to null (unless already changed)
            if ($convocation->getSitting() === $this) {
                $convocation->setSitting(null);
            }
        }

        return $this;
    }
}
