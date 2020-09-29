<?php

namespace App\Entity;

use App\Repository\RoleRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=RoleRepository::class)
 * @UniqueEntity("name")
 */
class Role
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $id;


    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $name;

    /**
     * @ORM\Column(type="json", options={"jsonb"=true})
     */
    private $composites = [];

    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getComposites(): array
    {
        return $this->composites;
    }

    public function setComposites(array $composites): self
    {
        $this->composites = $composites;

        return $this;
    }

    public function addComposite(string $composite): self
    {
        if (!in_array($composite, $this->composites)) {
            $this->composites[] = $composite;
        }

        return $this;
    }
}
