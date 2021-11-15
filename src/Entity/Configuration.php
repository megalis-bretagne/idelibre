<?php

namespace App\Entity;

use App\Repository\ConfigurationRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;

#[Entity(repositoryClass: ConfigurationRepository::class)]
class Configuration
{
    #[Id]
    #[GeneratedValue(strategy: 'UUID')]
    #[Column(type: 'guid')]
    private $id;

    #[Column(type: 'boolean', nullable: true)]
    private $isSharedAnnotation;

    #[OneToOne(inversedBy: 'configuration', targetEntity: Structure::class, cascade: ['persist', 'remove'])]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private $structure;

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
}
