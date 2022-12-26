<?php

namespace App\Entity;

use App\Repository\ConfigurationRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;
use phpDocumentor\Reflection\Types\String_;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Symfony\Component\Validator\Constraints as Constraint;

#[Entity(repositoryClass: ConfigurationRepository::class)]
class Configuration
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private $id;

    #[Column(type: 'boolean', nullable: true)]
    private $isSharedAnnotation;

    #[OneToOne(inversedBy: 'configuration', targetEntity: Structure::class, cascade: ['persist', 'remove'])]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private $structure;

    #[ORM\Column(length: 255, nullable: false, options: ['default' => '10000 years'])]
    #[Constraint\NotBlank]
    private ?string $sittingSuppressionDelay = null;



    public function getId(): ?string
    {
        return $this->id;
    }

    public function getIsSharedAnnotation(): ?bool
    {
        return $this->isSharedAnnotation;
    }

    public function setIsSharedAnnotation(?bool $isSharedAnnotation): self
    {
        $this->isSharedAnnotation = $isSharedAnnotation;

        return $this;
    }

    public function getStructure(): ?Structure
    {
        return $this->structure;
    }

    public function setStructure(?Structure $structure): self
    {
        $this->structure = $structure;

        return $this;
    }

    public function getSittingSuppressionDelay(): ?string
    {
        return $this->sittingSuppressionDelay;
    }

    public function setSittingSuppressionDelay(?string $sittingSuppressionDelay): self
    {
        $this->sittingSuppressionDelay = $sittingSuppressionDelay;

        return $this;
    }


}


