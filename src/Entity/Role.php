<?php

namespace App\Entity;

use App\Repository\RoleRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=RoleRepository::class)
 * @UniqueEntity("name")
 * @UniqueEntity("prettyName")
 */
class Role
{
    public const CODE_ROLE_SECRETARY = 1;
    public const CODE_ROLE_STRUCTURE_ADMIN = 2;
    public const CODE_ROLE_ACTOR = 3;

    public const NAME_ROLE_SECRETARY = 'Secretary';
    public const NAME_ROLE_STRUCTURE_ADMINISTRATOR = 'Admin';
    public const NAME_ROLE_ACTOR = 'Actor';
    public const NAME_ROLE_EMPLOYEE = 'Employee';
    public const NAME_ROLE_GUEST = 'Guest';
    public const INVITABLE_EMPLOYEE = [self::NAME_ROLE_EMPLOYEE, self:: NAME_ROLE_SECRETARY, self::NAME_ROLE_STRUCTURE_ADMINISTRATOR];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank
     * @Assert\Length(max="255")
     */
    private $name;

    /**
     * @ORM\Column(type="json", options={"jsonb"=true})
     */
    private $composites = [];

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Assert\NotBlank
     * @Assert\Length(max="255")
     */
    private $prettyName;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isInStructureRole = true;

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

    public function getPrettyName(): ?string
    {
        return $this->prettyName;
    }

    public function setPrettyName(?string $prettyName): self
    {
        $this->prettyName = $prettyName;

        return $this;
    }

    public function getIsInStructureRole(): ?bool
    {
        return $this->isInStructureRole;
    }

    public function setIsInStructureRole(bool $isInStructureRole): self
    {
        $this->isInStructureRole = $isInStructureRole;

        return $this;
    }
}
