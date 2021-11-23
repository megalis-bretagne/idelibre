<?php

namespace App\Entity;

use App\Entity\Gdpr\DataControllerGdpr;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

#[Entity(repositoryClass: 'App\Repository\StructureRepository')]
#[UniqueEntity('name')]
#[UniqueEntity('suffix')]
class Structure
{
    #[Id]
    #[GeneratedValue(strategy: 'UUID')]
    #[Column(type: 'guid')]
    private $id;

    #[Column(type: 'string', length: 255, unique: true)]
    #[NotBlank]
    #[Length(max: '255')]
    private $name;

    #[Column(type: 'string', length: 255, nullable: false)]
    #[NotBlank]
    #[Length(max: '255')]
    #[Email]
    private $replyTo;

    #[Column(type: 'string', length: 255, nullable: true)]
    #[Length(max: '255')]
    private $siren;

    #[OneToMany(mappedBy: 'structure', targetEntity: User::class)]
    private $users;

    #[ManyToOne(targetEntity: Group::class, inversedBy: 'structures')]
    #[JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private $group;

    #[ManyToOne(targetEntity: Timezone::class)]
    #[JoinColumn(nullable: false)]
    #[NotNull]
    private $timezone;

    #[Column(type: 'string', length: 255, unique: true)]
    #[NotBlank]
    #[Length(max: '255')]
    private $suffix;

    #[Column(type: 'string', length: 255)]
    private $legacyConnectionName;

    #[OneToOne(mappedBy: 'structure', targetEntity: DataControllerGdpr::class, cascade: ['persist', 'remove'])]
    private $dataControllerGdpr;

    #[OneToOne(mappedBy: 'structure', targetEntity: Configuration::class, cascade: ['persist', 'remove'])]
    private $configuration;

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

    public function getSiren(): ?string
    {
        return $this->siren;
    }

    public function setSiren($siren): self
    {
        $this->siren = $siren;

        return $this;
    }

    public function getLegacyConnectionName(): ?string
    {
        return $this->legacyConnectionName;
    }

    public function setLegacyConnectionName(string $legacyConnectionName): self
    {
        $this->legacyConnectionName = $legacyConnectionName;

        return $this;
    }

    public function getDataControllerGdpr(): ?DataControllerGdpr
    {
        return $this->dataControllerGdpr;
    }

    public function getConfiguration(): ?Configuration
    {
        return $this->configuration;
    }
}
