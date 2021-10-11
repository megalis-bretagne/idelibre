<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\TypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=TypeRepository::class)
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(
 *     name="IDX_TYPE_NAME_STRUCTURE",
 *     columns={"name", "structure_id"} )})
 *
 * @UniqueEntity(
 *     fields={"name", "structure"},
 *     errorPath="name",
 *     message="Ce type est déja utilisé dans cette structure")
 */
#[ApiResource(
    collectionOperations: ['get', 'post'],
    itemOperations: [
        'get' => [
            'normalization_context' => ['groups' => ['type:item:get', 'type:read']]
        ],
        'put' => [
            'normalization_context' => ['groups' => ['type:item:get', 'type:read']]
        ],
        'delete'
    ],
    shortName: 'types',
    attributes: ['order' => ['name' => 'ASC']],
    denormalizationContext: ['groups' => ['type:write']],
    normalizationContext: ['groups' => ['type:read']]
)]
class Type
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     * @Groups({"sitting"})
     */
    #[Groups(['type:read'])]
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Assert\NotBlank()
     * @Assert\Length(max="255")
     * @Groups({"sitting"})
     */
    #[Groups(['type:read', 'type:write'])]
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="associatedTypes")
     * @ORM\OrderBy({"lastName"="ASC"})
     */
    #[Groups(['type:item:get', 'type:write'])]
    private $associatedUsers;

    /**
     * @ORM\OneToOne(targetEntity=EmailTemplate::class, mappedBy="type")
     */
    #[Groups(['type:read'])]
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
     * @ORM\OrderBy({"lastName"="ASC"})
     */
    #[Groups(['type:item:get', 'type:write'])]
    private $authorizedSecretaries;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"sitting"})
     */
    #[Groups(['type:read', 'type:write'])]
    private $isSms;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"sitting"})
     */
    #[Groups(['type:read', 'type:write'])]
    private $isComelus;

    /**
     * @ORM\OneToOne(targetEntity=Reminder::class, mappedBy="type", cascade={"persist", "remove"})
     */
    #[Groups(['type:read'])]
    private $reminder;

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
     * @return Collection<User>
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

    /**
     * @return mixed
     */
    public function getEmailTemplate()
    {
        return $this->emailTemplate;
    }

    /**
     * @return Collection<User>
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

    public function getIsSms(): bool
    {
        return $this->isSms ?? false;
    }

    public function setIsSms(bool $isSms): self
    {
        $this->isSms = $isSms;

        return $this;
    }

    public function getIsComelus(): bool
    {
        return $this->isComelus ?? false;
    }

    public function setIsComelus(?bool $isComelus): self
    {
        $this->isComelus = $isComelus;

        return $this;
    }

    public function getReminder(): ?Reminder
    {
        return $this->reminder;
    }

    public function setReminder(Reminder $reminder): self
    {
        // set the owning side of the relation if necessary
        if ($reminder->getType() !== $this) {
            $reminder->setType($this);
        }

        $this->reminder = $reminder;

        return $this;
    }
}
