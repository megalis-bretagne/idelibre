<?php

namespace App\Entity;

use App\Repository\ReminderRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Symfony\Component\Serializer\Annotation\Groups;

#[Entity(repositoryClass: ReminderRepository::class)]
class Reminder
{
    public const VALUES = [
        '30 minutes' => 30,
        '1 heure' => 60,
        '90 minutes' => 90,
        '2 heures' => 120,
        '3 heures' => 180,
        '4 heures' => 240,
        '5 heures' => 300,
    ];
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups(['type:detail', 'type:write', 'sitting:detail'])]
    private $id;

    #[Column(type: 'integer')]
    #[Groups(['type:detail', 'type:write', 'sitting:detail'])]
    private $duration = 60;

    #[Column(type: 'boolean')]
    #[Groups(['type:detail', 'type:write', 'sitting:detail'])]
    private $isActive = false;

    #[OneToOne(inversedBy: 'reminder', targetEntity: Sitting::class, cascade: ['persist', 'remove'])]
    #[JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private $sitting;

    #[OneToOne(inversedBy: 'reminder', targetEntity: Type::class, cascade: ['persist', 'remove'])]
    #[JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private $type;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): self
    {
        if (!$duration) {
            return $this;
        }

        $this->duration = $duration;

        return $this;
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

    public function getSitting(): ?Sitting
    {
        return $this->sitting;
    }

    public function setSitting(Sitting $sitting): self
    {
        $this->sitting = $sitting;

        return $this;
    }

    public function getType(): ?Type
    {
        return $this->type;
    }

    public function setType(Type $type): self
    {
        $this->type = $type;

        return $this;
    }
}
