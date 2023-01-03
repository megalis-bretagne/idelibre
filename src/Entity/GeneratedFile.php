<?php

namespace App\Entity;

use App\Repository\GeneratedFileRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

#[ORM\Entity(repositoryClass: GeneratedFileRepository::class)]
#[Table(name: '`generated_file`')]
#[UniqueEntity(fields: ['sitting', 'type'], message: 'Le doit être unique par séance', errorPath: 'type')]
#[UniqueConstraint(name: 'IDX_GENERATED_FILE_SITTING_TYPE', columns: ['sitting_id', 'type'])]
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

    #[ORM\Column(length: 3)]
    #[NotBlank]
    #[Length(max: '3')]
    #[Choice([self::ZIP, self::PDF])]
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
