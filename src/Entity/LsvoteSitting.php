<?php

namespace App\Entity;

use App\Repository\LsvoteSittingRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Symfony\Component\Validator\Constraints\Uuid;

#[ORM\Entity(repositoryClass: LsvoteSittingRepository::class)]
class LsvoteSitting
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?int $id = null;

    #[ORM\Column(type: 'uuid')]
    private ?Uuid $LsvoteSittingId = null;

    #[Column(type: 'json')]
    private array $results = [];

    #[ORM\Column]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\OneToOne(inversedBy: 'lsvoteSitting', cascade: ['persist', 'remove'])]
    private ?Sitting $sitting = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLsvoteSittingId(): ?Uuid
    {
        return $this->LsvoteSittingId;
    }

    public function setLsvoteSittingId(Uuid $LsvoteSittingId): self
    {
        $this->LsvoteSittingId = $LsvoteSittingId;

        return $this;
    }

    public function getResults(): array
    {
        return $this->results;
    }

    public function setResults(array $results): self
    {
        $this->results = $results;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): self
    {
        $this->createdAt = new DateTimeImmutable('now');

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
