<?php

namespace App\Entity;

use App\Repository\TimestampRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

#[Entity(repositoryClass: TimestampRepository::class)]
class Timestamp
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups(['convocation:read'])]
    private $id;


    #[Column(type: 'datetime')]
    #[Groups(['convocation', 'convocation:read'])]
    private $createdAt;

    /**
     * @var string|null
     */
    #[Column(type: 'string', length: 255)]
    #[NotBlank]
    #[Length(max: '255')]
    private ?string $filePathContent;

    /**
     * @var string|null
     */
    #[Column(type: 'string', length: 255, nullable: true)]
    #[Length(max: '255')]
    private ?string $filePathTsa;

    #[ManyToOne(targetEntity: Sitting::class, inversedBy: 'updatedTimestamps')]
    #[JoinColumn(onDelete: 'CASCADE')]
    private ?Sitting $sitting;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getFilePathContent(): ?string
    {
        return $this->filePathContent;
    }

    public function setFilePathContent(?string $filePathContent): Timestamp
    {
        $this->filePathContent = $filePathContent;

        return $this;
    }

    public function getFilePathTsa(): ?string
    {
        return $this->filePathTsa;
    }

    public function setFilePathTsa(?string $filePathTsa): Timestamp
    {
        $this->filePathTsa = $filePathTsa;

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
}
