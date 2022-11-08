<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Doctrine\ORM\Mapping as ORM;


#[Entity(repositoryClass: 'App\Repository\GroupRepository')]
#[UniqueEntity(fields: 'name')]
#[Table(name: '`group`')]
class Group
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private $id;

    #[Column(type: 'string', length: 255, unique: true)]
    #[Length(max: '255')]
    #[NotBlank]
    private $name;

    #[OneToMany(mappedBy: 'group', targetEntity: User::class)]
    private $users;

    #[OneToMany(mappedBy: 'group', targetEntity: Structure::class)]
    private $structures;

    #[Column(type: 'boolean')]
    private $isStructureCreator = false;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->structures = new ArrayCollection();
    }

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

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setGroup($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            // set the owning side to null (unless already changed)
            if ($user->getGroup() === $this) {
                $user->setGroup(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Structure[]
     */
    public function getStructures(): Collection
    {
        return $this->structures;
    }

    public function addStructure(Structure $structure): self
    {
        if (!$this->structures->contains($structure)) {
            $this->structures[] = $structure;
            $structure->setGroup($this);
        }

        return $this;
    }

    public function removeStructure(Structure $structure): self
    {
        if ($this->structures->contains($structure)) {
            $this->structures->removeElement($structure);
            // set the owning side to null (unless already changed)
            if ($structure->getGroup() === $this) {
                $structure->setGroup(null);
            }
        }

        return $this;
    }

    public function getIsStructureCreator(): ?bool
    {
        return $this->isStructureCreator;
    }

    public function setIsStructureCreator(bool $isStructureCreator): self
    {
        $this->isStructureCreator = $isStructureCreator;

        return $this;
    }
}
