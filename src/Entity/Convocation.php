<?php

namespace App\Entity;

use App\Repository\ConvocationRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use InvalidArgumentException;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

#[Entity(repositoryClass: ConvocationRepository::class)]
class Convocation
{
    public const CATEGORY_CONVOCATION = 'convocation';
    public const CATEGORY_INVITATION = 'invitation';
    public const PRESENT = 'present';
    public const REMOTE = 'remote';
    public const ABSENT = 'absent';
    public const ABSENT_GIVE_POA = 'poa'; # POA est l'acronyme de "power of attorney" qui signifie procuration
    public const ABSENT_SEND_DEPUTY = 'deputy';
    public const UNDEFINED = '';

    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
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

    #[Column(type: 'string', length: 255, nullable: true, options: ['default' => self::UNDEFINED])]
    #[Groups(groups: ['convocation', 'convocation:read'])]
    private $attendance;


    #[ORM\OneToOne(mappedBy: 'convocation', cascade: ['persist', 'remove'])]
    private ?AttendanceToken $attendanceToken = null;

    #[ORM\ManyToOne()]
    #[Groups(groups: ['user', 'convocation', 'convocation:read'])]
    private ?User $deputy = null;

    #[ORM\ManyToOne()]
    #[Groups(groups: ['user', 'convocation', 'convocation:read'])]
    private ?User $mandator = null;

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
        if (!in_array($attendance, [self::PRESENT, self::ABSENT, self::UNDEFINED, self::REMOTE, self::ABSENT_GIVE_POA, self::ABSENT_SEND_DEPUTY])) {
            throw new InvalidArgumentException('attendance not allowed');
        }
        $this->attendance = $attendance;

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

    public function getAttendanceToken(): ?AttendanceToken
    {
        return $this->attendanceToken;
    }

    public function setAttendanceToken(AttendanceToken $attendanceToken): self
    {
        // set the owning side of the relation if necessary
        if ($attendanceToken->getConvocation() !== $this) {
            $attendanceToken->setConvocation($this);
        }

        $this->attendanceToken = $attendanceToken;

        return $this;
    }

    public function getDeputy(): ?User
    {
        return $this->deputy;
    }

    public function setDeputy(?User $deputy): static
    {
        $this->deputy = $deputy;

        return $this;
    }

    public function getMandator(): ?User
    {
        return $this->mandator;
    }

    public function setMandator(?User $mandator): static
    {
        $this->mandator = $mandator;

        return $this;
    }
}
