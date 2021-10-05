<?php

namespace App\Entity;

use App\Repository\TimestampRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=TimestampRepository::class)
 */
class Timestamp
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     *
     * @var string|null
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"convocation"})
     *
     * @var DateTimeInterface
     */
    private $createdAt;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @Assert\Length(max="255")
     *
     * @var string|null
     */
    private $filePathContent;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max="255")
     *
     * @var string|null
     */
    private $filePathTsa;

    /**
     * @ORM\ManyToOne(targetEntity=Sitting::class, inversedBy="updatedTimestamps")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $sitting;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?int
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
