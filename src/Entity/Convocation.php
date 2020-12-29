<?php

namespace App\Entity;

use App\Repository\ConvocationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ConvocationRepository::class)
 */
class Convocation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     * @Groups({"convocation"})
     */
    private $id;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"convocation"})
     */
    private $isRead = false;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"convocation"})
     */
    private $isActive = false;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"convocation"})
     */
    private $isEmailed = false;

    /**
     * @ORM\ManyToOne(targetEntity=Sitting::class, inversedBy="convocations")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Assert\NotNull
     */
    private $sitting;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"convocation"})
     * @Assert\NotNull
     */
    private $actor;

    /**
     * @ORM\ManyToOne(targetEntity=Timestamp::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="SET NULL")
     * @Groups({"convocation"})
     */
    private $sentTimestamp;

    /**
     * @ORM\OneToOne(targetEntity=Timestamp::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="SET NULL")
     * @Groups({"convocation"})
     */
    private $receivedTimestamp;

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

    public function getActor(): ?User
    {
        return $this->actor;
    }

    public function setActor(?User $actor): self
    {
        $this->actor = $actor;

        return $this;
    }

    public function getSentTimestamp(): ?Timestamp
    {
        return $this->sentTimestamp;
    }

    public function setSentTimestamp(?Timestamp $sentTimestamp): self
    {
        $this->sentTimestamp = $sentTimestamp;

        return $this;
    }

    public function getReceivedTimestamp(): ?Timestamp
    {
        return $this->receivedTimestamp;
    }

    public function setReceivedTimestamp(?Timestamp $receivedTimestamp): self
    {
        $this->receivedTimestamp = $receivedTimestamp;

        return $this;
    }
}
