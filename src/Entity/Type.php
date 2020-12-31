<?php

namespace App\Entity;

use App\Repository\TypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=TypeRepository::class)
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="IDX_TYPE_NAME_STRUCTURE", columns={"name", "structure_id"})})
 *
 * @UniqueEntity(
 *     fields={"name", "structure"},
 *     errorPath="name",
 *     message="Ce type est déja utilisé dans cette structure")
 */
class Type
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Assert\NotBlank()
     * @Assert\Length(max="255")
     */
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="associatedTypes")
     */
    private $associatedUsers;

    /**
     * @ORM\OneToOne(targetEntity=EmailTemplate::class, mappedBy="type")
     */
    private $emailTemplate;

    /**
     * @ORM\ManyToOne(targetEntity=Structure::class)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Assert\NotNull
     */
    private $structure;

    /**
     * @ORM\JoinTable(name="type_secretary")
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="authorizedTypes")
     */
    private $authorizedSecretaries;

    public function __construct()
    {
        $this->associatedUsers = new ArrayCollection();
        $this->authorizedSecretaries = new ArrayCollection();
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
    public function getAssociatedUsers(): Collection
    {
        return $this->associatedUsers;
    }

    public function setAssociatedUsers(iterable $users): self
    {
        $this->associatedUsers = $users;

        return $this;
    }

    //TODO check if useless ?
    public function addAssociatedUser(User $associatedUser): self
    {
        if (!$this->associatedUsers->contains($associatedUser)) {
            $this->associatedUsers[] = $associatedUser;
        }

        return $this;
    }

    //TODO check if useless ?
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

    /**
     * @return mixed
     */
    public function getEmailTemplate()
    {
        return $this->emailTemplate;
    }

    /**
     * @return Collection|User[]
     */
    public function getAuthorizedSecretaries(): Collection
    {
        return $this->authorizedSecretaries;
    }

    public function addAuthorizedSecretary(User $authorizedSecretary): self
    {
        if (!$this->authorizedSecretaries->contains($authorizedSecretary)) {
            $this->authorizedSecretaries[] = $authorizedSecretary;
        }

        return $this;
    }

    public function removeAuthorizedSecretary(User $authorizedSecretary): self
    {
        $this->authorizedSecretaries->removeElement($authorizedSecretary);

        return $this;
    }
}
