<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\CustomIdGenerator;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

#[Entity(repositoryClass: 'App\Repository\TimezoneRepository')]
#[UniqueEntity('name')]
class Timezone
{
    #[Id]
    #[GeneratedValue(strategy: 'CUSTOM')]
    #[CustomIdGenerator(UuidGenerator::class)]
    #[Column(type: 'uuid', unique: true)]
    private $id;

    #[Column(type: 'string', length: 255, unique: true)]
    #[NotBlank]
    #[Length(max: '255')]
    private $name;

    #[Column(type: 'string', length: 255, nullable: true)]
    #[Length(max: '255')]
    private $info;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getInfo(): ?string
    {
        return $this->info;
    }

    public function setInfo(string $info): self
    {
        $this->info = $info;

        return $this;
    }
}
