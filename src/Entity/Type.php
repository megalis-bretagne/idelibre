<?php

namespace App\Entity;

use App\Repository\TypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TypeRepository::class)
 */
class Type
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="associatedTypes")
     */
    private $associatedUsers;

    /**
     * @ORM\ManyToOne(targetEntity=Structure::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $structure;

    public function __construct()
    {
        $this->associatedUsers = new ArrayCollection();
    }

    public function getId(): ?int
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

    /**
     * @return Collection|User[]
     */
    public function getAssociatedUsers(): Collection
    {
        return $this->associatedUsers;
    }

    public function addAssociatedUser(User $associatedUser): self
    {
        if (!$this->associatedUsers->contains($associatedUser)) {
            $this->associatedUsers[] = $associatedUser;
        }

        return $this;
    }

    public function removeAssociatedUser(User $associatedUser): self
    {
        if ($this->associatedUsers->contains($associatedUser)) {
            $this->associatedUsers->removeElement($associatedUser);
        }

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
