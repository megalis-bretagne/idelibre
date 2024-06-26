<?php

namespace App\Entity;

use App\Repository\ThemeRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OrderBy;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

#[Gedmo\Tree(type: 'nested')]
#[Entity(repositoryClass: ThemeRepository::class)]
#[Index(columns: ['lft'], name: 'lft_ix')]
#[Index(columns: ['rgt'], name: 'rgt_ix')]
#[Index(columns: ['lvl'], name: 'lvl_ix')]
#[ORM\HasLifecycleCallbacks]
class Theme
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups(['theme', 'theme:read', 'project:read'])]
    private $id;

    #[Column(type: 'string', length: 255)]
    #[NotBlank]
    #[Length(max: 255)]
    #[Groups(['theme', 'theme:read', 'theme:write', 'project:read'])]
    private ?string $name;

    #[Gedmo\TreeLeft]
    #[Column(type: 'integer')]
    private $lft;

    #[Gedmo\TreeLevel]
    #[Column(type: 'integer')]
    #[Groups(['theme'])]
    private $lvl;

    #[Gedmo\TreeRight]
    #[Column(type: 'integer')]
    private $rgt;

    #[Gedmo\TreeRoot]
    #[ManyToOne(targetEntity: Theme::class)]
    #[JoinColumn(name: 'tree_root', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private $root;

    #[Gedmo\TreeParent]
    #[ManyToOne(targetEntity: Theme::class, inversedBy: 'children')]
    #[JoinColumn(name: 'parent_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[Groups(['theme:write:post'])]
    private $parent;

    #[OneToMany(mappedBy: 'parent', targetEntity: Theme::class)]
    #[OrderBy(value: ['name' => 'ASC'])]
    private $children;

    #[ManyToOne(targetEntity: Structure::class)]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[NotNull]
    private $structure;

    #[Column(type: 'string', length: 512, nullable: true)]
    #[Groups(['theme', 'theme:read', 'project:read'])]
    private $fullName;


    #[ORM\Column(nullable: true)]
    #[Groups(['theme:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['theme:read'])]
    private ?\DateTimeImmutable $updatedAt = null;


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
    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getParent(): ?Theme
    {
        return $this->parent;
    }

    public function setParent(?Theme $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function getRoot(): Theme
    {
        return $this->root;
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

    public function getLvl(): int
    {
        return $this->lvl;
    }

    public function getLft(): int
    {
        return $this->lft;
    }

    public function getRgt(): int
    {
        return $this->rgt;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(?string $fullName): self
    {
        $this->fullName = $fullName;

        return $this;
    }


    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->setUpdatedAtValue();
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }



    #[Groups(['theme:read'])]
    #[SerializedName('parentId')]
    public function getParentId(): ?string
    {
        if (!$this->parent || $this->parent->getName() === 'ROOT') {
            return null;
        }

        return $this->parent->getId();
    }
}
