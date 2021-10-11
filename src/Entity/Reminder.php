<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use App\Repository\ReminderRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ReminderRepository::class)
 */
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

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    #[ApiProperty(attributes: [
        'openapi_context' => [
            'type' => 'integer',
            'enum' => [60, 90, 120, 180, 240, 300],
        ],
    ])]
    #[Assert\Choice(choices: [60, 90, 120, 180, 240, 300])]
    #[Groups(['type:item:get', 'type:write'])]
    private $duration = 60;

    /**
     * @ORM\Column(type="boolean")
     */
    #[Groups(['type:item:get', 'type:write'])]
    private $isActive = false;

    /**
     * @ORM\OneToOne(targetEntity=Sitting::class, inversedBy="reminder", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private $sitting;

    /**
     * @ORM\OneToOne(targetEntity=Type::class, inversedBy="reminder", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
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
