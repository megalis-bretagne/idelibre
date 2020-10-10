<?php

namespace App\Entity;

use App\Repository\TimestampRepository;
use Doctrine\ORM\Mapping as ORM;

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

    /**
     * @ORM\OneToOne(targetEntity=Convocation::class, inversedBy="timestamp", cascade={"persist", "remove"})
     */
    private $convocation;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreateAt(): ?\DateTimeInterface
    {
        return $this->createAt;
    }

    public function setCreateAt(\DateTimeInterface $createAt): self
    {
        $this->createAt = $createAt;

        return $this;
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

    public function getConvocation(): ?Convocation
    {
        return $this->convocation;
    }

    public function setConvocation(?Convocation $convocation): self
    {
        $this->convocation = $convocation;

        return $this;
    }
}
