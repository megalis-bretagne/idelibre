<?php

namespace App\Entity;

use App\Repository\ApiRoleRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;

#[Entity(repositoryClass: ApiRoleRepository::class)]
class ApiRole
{
    #[Id]
    #[GeneratedValue(strategy: 'UUID')]
    #[Column(type: 'guid')]
    private $id;

    #[Column(type: 'string', length: 255)]
    private $name;

    #[Column(type: 'json', options: ['jsonb' => true])]
    private $composites = [];

    #[Column(type: 'string', length: 255)]
    private $prettyName;

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

    public function getComposites(): ?array
    {
        return $this->composites;
    }

    public function setComposites(array $composites): self
    {
        $this->composites = $composites;

        return $this;
    }

    public function getPrettyName(): ?string
    {
        return $this->prettyName;
    }

    public function setPrettyName(string $prettyName): self
    {
        $this->prettyName = $prettyName;

        return $this;
    }
}
