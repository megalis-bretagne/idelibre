<?php

namespace App\Entity;

use App\Repository\ApiUserRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

#[Entity(repositoryClass: ApiUserRepository::class)]
class ApiUser implements UserInterface
{
    #[Id]
    #[GeneratedValue(strategy: 'UUID')]
    #[Column(type: 'guid')]
    private $id;

    #[Column(type: 'string', length: 255)]
    #[NotBlank]
    #[NotNull]
    #[Length(max: 255)]
    private $name;

    #[Column(type: 'string', length: 255)]
    #[NotBlank]
    #[NotNull]
    #[Length(max: 255)]
    private $token;

    #[ManyToOne(targetEntity: Structure::class)]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[NotNull]
    private $structure;

    #[ManyToOne(targetEntity: ApiRole::class)]
    #[JoinColumn(nullable: false)]
    #[NotNull]
    private $apiRole;

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

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

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

    public function getRoles(): array
    {
        $roles = $this->getApiRole() ? $this->getApiRole()->getComposites() : [];

        return array_unique($roles);
    }

    public function getPassword()
    {
        return null;
    }

    public function getSalt()
    {
        return null;
    }

    public function eraseCredentials()
    {
        // Not implemented
    }

    public function getUsername()
    {
        return $this->name;
    }

    public function getApiRole(): ?ApiRole
    {
        return $this->apiRole;
    }

    public function setApiRole(?ApiRole $apiRole): self
    {
        $this->apiRole = $apiRole;

        return $this;
    }
}
