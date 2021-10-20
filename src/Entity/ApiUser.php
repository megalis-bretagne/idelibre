<?php

namespace App\Entity;

use App\Repository\ApiUserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=ApiUserRepository::class)
 */
class ApiUser implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $token;

    /**
     * @ORM\ManyToOne(targetEntity=Structure::class)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $structure;

    /**
     * @ORM\ManyToOne(targetEntity=ApiRole::class)
     * @ORM\JoinColumn(nullable=false)
     */
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
