<?php

namespace App\Entity;

use App\Repository\LsvoteSittingRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints\Uuid;

#[ORM\Entity(repositoryClass: LsvoteSittingRepository::class)]
class LsvoteSitting
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    #[ORM\Column(type: 'string')]
    #[Groups(groups: ['sitting'])]
    private ?string $lsvoteSittingId = null;

    #[Column(type: 'json', options: ['jsonb' => true])]
    private array $results = [];

    #[ORM\Column]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\OneToOne(inversedBy: 'lsvoteSitting', cascade: ['persist', 'remove'])]
    private ?Sitting $sitting = null;


    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable('now');
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getLsvoteSittingId(): ?string
    {
        return $this->lsvoteSittingId;
    }

    public function setLsvoteSittingId(string $lsvoteSittingId): self
    {
        $this->lsvoteSittingId = $lsvoteSittingId;

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
