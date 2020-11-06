<?php

namespace App\Entity;

use App\Repository\TimestampRepository;
use DateTimeImmutable;
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
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"convocation"})
     */
    private $createAt;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $tsa;



    public function __construct()
    {
        $this->createAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreateAt(): ?\DateTimeInterface
    {
        return $this->createAt;
    }


    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getTsa(): ?string
    {
        return $this->tsa;
    }

    public function setTsa(?string $tsa): self
    {
        $this->tsa = $tsa;

        return $this;
    }
}
