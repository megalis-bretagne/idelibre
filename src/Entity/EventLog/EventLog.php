<?php

namespace App\Entity\EventLog;

use App\Repository\EventLogRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;

#[ORM\Entity(repositoryClass: EventLogRepository::class)]
class EventLog
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    #[ORM\Column(length: 255)]
    private ?string $authorId = null;

    #[ORM\Column(length: 255)]
    private ?string $authorName = null;

    #[ORM\Column(length: 255, enumType: Action::class)]
    private ?Action $action = null;

    #[ORM\Column(length: 255)]
    private ?string $targetId = null;

    #[ORM\Column(length: 255)]
    private ?string $targetName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $structureId = null;

    #[ORM\Column]
    private ?DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getAuthorId(): ?string
    {
        return $this->authorId;
    }

    public function setAuthorId(string $authorId): self
    {
        $this->authorId = $authorId;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getAuthorName(): ?string
    {
        return $this->authorName;
    }

    public function setAuthorName(string $authorName): self
    {
        $this->authorName = $authorName;

        return $this;
    }

    public function getAction(): ?Action
    {
        return $this->action;
    }

    public function setAction(Action $action): self
    {
        $this->action = $action;

        return $this;
    }

    public function getTargetId(): ?string
    {
        return $this->targetId;
    }

    public function setTargetId(string $targetId): self
    {
        $this->targetId = $targetId;

        return $this;
    }

    public function getTargetName(): ?string
    {
        return $this->targetName;
    }

    public function setTargetName(string $targetName): self
    {
        $this->targetName = $targetName;

        return $this;
    }

    public function getStructureId(): ?string
    {
        return $this->structureId;
    }

    public function setStructureId(?string $structureId): self
    {
        $this->structureId = $structureId;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }
}
