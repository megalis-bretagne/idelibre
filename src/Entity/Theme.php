<?php

namespace App\Entity;

use App\Repository\ThemeRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;

//* @ORM\Entity(repositoryClass=ThemeRepository::class)
//@ORM\Entity(repositoryClass="Gedmo\Tree\Entity\Repository\NestedTreeRepository")

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
class Theme
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     * @Groups({"theme"})
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     * @Groups({"theme"})
     */
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
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Theme", mappedBy="parent")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private $children;

    /**
     * @ORM\ManyToOne(targetEntity=Structure::class)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $structure;

    /**
     * @ORM\Column(type="string", length=512, nullable=true)
     * @Groups({"theme"})
     */
    private $fullName;

    public function getId(): string
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

    public function getParent(): Theme
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