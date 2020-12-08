<?php

namespace App\Entity;

use App\Repository\TimestampRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=TimestampRepository::class)
 */
class Timestamp
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     * @var string | null
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"convocation"})
     * @var DateTimeInterface
     */
    private $createAt;

    /**
     * @ORM\Column(type="text")
     * @var string | null
     */
    private $filePathContent;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string | null
     */
    private $filePathTsa;



    public function __construct()
    {
        $this->createAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreateAt(): ?DateTimeInterface
    {
        return $this->createAt;
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
}
