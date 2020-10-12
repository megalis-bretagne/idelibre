<?php

namespace App\Entity;

use App\Repository\ConvocationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ConvocationRepository::class)
 */
class Convocation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isRead = false;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isActive = false;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isEmailed = false;

    /**
     * @ORM\ManyToOne(targetEntity=Sitting::class, inversedBy="convocations")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $sitting;

    /**
     * @ORM\OneToOne(targetEntity=Timestamp::class, mappedBy="convocation", cascade={"persist", "remove"})
     */
    private $timestamp;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $actor;


    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getIsRead(): ?bool
    {
        return $this->isRead;
    }

    public function setIsRead(bool $isRead): self
    {
        $this->isRead = $isRead;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getIsEmailed(): ?bool
    {
        return $this->isEmailed;
    }

    public function setIsEmailed(?bool $isEmailed): self
    {
        $this->isEmailed = $isEmailed;

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

    public function getTimestamp(): ?Timestamp
    {
        return $this->timestamp;
    }

    public function setTimestamp(?Timestamp $timestamp): self
    {
        $this->timestamp = $timestamp;

        // set (or unset) the owning side of the relation if necessary
        $newConvocation = null === $timestamp ? null : $this;
        if ($timestamp->getConvocation() !== $newConvocation) {
            $timestamp->setConvocation($newConvocation);
        }

        return $this;
    }

    public function getActor(): ?User
    {
        return $this->actor;
    }

    public function setActor(?User $actor): self
    {
        $this->actor = $actor;

        return $this;
    }
}
