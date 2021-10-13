<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\ThemeRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Gedmo\Tree(type="nested")
 * @ORM\Table(
 *     name="theme",
 *     indexes={
 *          @Index(name="lft_ix", columns={"lft"}),
 *          @Index(name="rgt_ix", columns={"rgt"}),
 *          @Index(name="lvl_ix", columns={"lvl"})
 *     })
 * @ORM\Entity(repositoryClass=ThemeRepository::class)
 */
#[ApiResource(
    collectionOperations: [
        'get',
        'post' => [
            'denormalization_context' => ['groups' => ['theme:collection:post', 'theme:write']],
        ],
    ],
    itemOperations: [
        'get' => [
            'normalization_context' => ['groups' => ['theme:item:get', 'theme:read']],
        ],
        'put' => [
            'normalization_context' => ['groups' => ['theme:item:get', 'theme:read']],
        ],
        'delete',
    ],
    shortName: 'themes',
    attributes: ['order' => ['name' => 'ASC']],
    denormalizationContext: ['groups' => ['theme:write']],
    normalizationContext: ['groups' => ['theme:read']]
)]
class Theme
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     * @Groups({"theme"})
     */
    #[Groups(['theme:read'])]
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"theme"})
     * @Assert\NotBlank
     * @Assert\Length(max="255")
     */
    #[Groups(['theme:read', 'theme:write'])]
    private $name;

    /**
     * @Gedmo\TreeLeft()
     * @ORM\Column(type="integer")
     */
    private $lft;

    /**
     * @Gedmo\TreeLevel()
     * @ORM\Column(type="integer")
     * @Groups({"theme"})
     */
    private $lvl;

    /**
     * @Gedmo\TreeRight()
     * @ORM\Column(type="integer")
     */
    private $rgt;

    /**
     * @Gedmo\TreeRoot()
     * @ORM\ManyToOne(targetEntity="App\Entity\Theme")
     * @ORM\JoinColumn(name="tree_root", referencedColumnName="id", onDelete="cascade")
     */
    private $root;

    /**
     * @Gedmo\TreeParent()
     * @ORM\ManyToOne(targetEntity="App\Entity\Theme", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="cascade")
     */
    #[Groups(['theme:collection:post'])]
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Theme", mappedBy="parent")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private $children;

    /**
     * @ORM\ManyToOne(targetEntity=Structure::class)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Assert\NotNull
     */
    private $structure;

    /**
     * @ORM\Column(type="string", length=512, nullable=true)
     * @Groups({"theme"})
     */
    #[Groups(['theme:read'])]
    private $fullName;

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

    public function setParent(Theme $parent): self
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

    public function setFullName(string $fullName): self
    {
        $this->fullName = $fullName;

        return $this;
    }
}
