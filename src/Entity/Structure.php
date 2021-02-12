<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StructureRepository")
 * @UniqueEntity("name")
 */
class Structure
{
    /**
     * @ORM\Id()
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
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Assert\NotBlank
     * @Assert\Length(max="255")
     * @Assert\Email
     */
    private $replyTo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max="255")
     */
    private $siren;

    /**
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="structure")
     */
    private $users;

    /**
     * @ORM\ManyToOne(targetEntity=Group::class, inversedBy="structures")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $group;

    /**
     * @ORM\ManyToOne(targetEntity=Timezone::class)
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull
     */
    private $timezone;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank
     * @Assert\Length(max="255")
     */
    private $suffix;

    public function __construct()
    {
        $this->users = new ArrayCollection();
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

    public function getReplyTo(): ?string
    {
        return $this->replyTo;
    }

    public function setReplyTo(?string $replyTo): self
    {
        $this->replyTo = $replyTo;

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
            $user->setStructure($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            // set the owning side to null (unless already changed)
            if ($user->getStructure() === $this) {
                $user->setStructure(null);
            }
        }

        return $this;
    }

    public function getGroup(): ?Group
    {
        return $this->group;
    }

    public function setGroup(?Group $group): self
    {
        $this->group = $group;

        return $this;
    }

    public function getTimezone(): ?Timezone
    {
        return $this->timezone;
    }

    public function setTimezone(?Timezone $timezone): self
    {
        $this->timezone = $timezone;

        return $this;
    }

    public function getSuffix(): ?string
    {
        return $this->suffix;
    }

    public function setSuffix(string $suffix): self
    {
        $this->suffix = $suffix;

        return $this;
    }

    public function getSiren(): string
    {
        return $this->siren;
    }

    public function setSiren($siren): self
    {
        $this->siren = $siren;

        return $this;
    }
}
