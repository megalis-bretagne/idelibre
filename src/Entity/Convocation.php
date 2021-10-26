<?php

namespace App\Entity;

use App\Repository\ConvocationRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use http\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

#[Entity(repositoryClass: ConvocationRepository::class)]
class Convocation
{
    public const CATEGORY_CONVOCATION = 'convocation';
    public const CATEGORY_INVITATION = 'invitation';
    public const PRESENT = 'present';
    public const ABSENT = 'absent';
    public const UNDEFINED = '';

    #[Id]
    #[GeneratedValue(strategy: 'UUID')]
    #[Column(type: 'guid')]
    #[Groups(groups: ['convocation', 'convocation:read'])]
    private $id;

    #[Column(type: 'boolean')]
    #[Groups(groups: ['convocation', 'convocation:read'])]
    private $isRead = false;

    #[Column(type: 'datetime')]
    private $createdAt;

    #[Column(type: 'boolean')]
    #[Groups(groups: ['convocation', 'convocation:read'])]
    private $isActive = false;

    #[Column(type: 'boolean', nullable: true)]
    #[Groups(groups: ['convocation', 'convocation:read'])]
    private $isEmailed = false;

    #[ManyToOne(targetEntity: Sitting::class, inversedBy: 'convocations')]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[NotNull]
    private $sitting;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(groups: ['convocation', 'convocation:read'])]
    #[NotNull]
    private $user;

    #[ManyToOne(targetEntity: Timestamp::class, cascade: ['persist', 'remove'])]
    #[JoinColumn(onDelete: 'SET NULL')]
    #[Groups(groups: ['convocation', 'convocation:read'])]
    private $sentTimestamp;

    #[OneToOne(targetEntity: Timestamp::class, cascade: ['persist', 'remove'])]
    #[JoinColumn(onDelete: 'SET NULL')]
    #[Groups(groups: ['convocation', 'convocation:read'])]
    private $receivedTimestamp;

    #[Column(type: 'string', length: 255)]
    #[Groups(groups: ['convocation', 'convocation:read'])]
    #[NotBlank]
    private $category;

    #[Column(type: 'string', length: 255, nullable: true)]
    #[Groups(groups: ['convocation', 'convocation:read'])]
    private $attendance;

    #[Column(type: 'string', length: 255, nullable: true)]
    #[Groups(groups: ['convocation', 'convocation:read'])]
    private $deputy;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
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

    public function getCreatedAt(): ?DateTimeInterface
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

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

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getAttendance(): ?string
    {
        return $this->attendance;
    }

    public function setAttendance(?string $attendance): self
    {
        if (!in_array($attendance, [self::PRESENT, self::ABSENT, self::UNDEFINED])) {
            throw new InvalidArgumentException('attendance not allowed');
        }
        $this->attendance = $attendance;

        return $this;
    }

    public function getDeputy(): ?string
    {
        return $this->deputy;
    }

    public function setDeputy(?string $deputy): self
    {
        $this->deputy = $deputy;

        return $this;
    }

    public function isConvocation(): bool
    {
        return self::CATEGORY_CONVOCATION === $this->category;
    }

    public function isInvitation(): bool
    {
        return self::CATEGORY_INVITATION === $this->category;
    }
}
