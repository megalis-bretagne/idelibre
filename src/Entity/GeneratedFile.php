<?php

namespace App\Entity;

use App\Repository\GeneratedFileRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

#[ORM\Entity(repositoryClass: GeneratedFileRepository::class)]
class GeneratedFile
{
    const PDF = 'pdf';
    const ZIP = 'zip';

    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id;

    #[ORM\ManyToOne(inversedBy: 'generatedFiles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Sitting $sitting;

    #[ORM\Column(length: 255)]
    #[NotBlank]
    #[Length(max: '255')]
    private string $type;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?File $file;

    public function __construct(string $type, Sitting $sitting, File $file)
    {
        $this->type = $type;
        $this->sitting = $sitting;
        $this->file = $file;
    }

    public function getId(): ?string
    {
        return $this->id;
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

    public function getType(): ?string
    {
        return $this->type;
    }



    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(File $file): self
    {
        $this->file = $file;

        return $this;
    }
}
