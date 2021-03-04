<?php

namespace App\Entity\Shop;

use App\Repository\Shop\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass=CategoryRepository::class)
 */
class Category
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column
     */
    private string $name;

    /**
     * @var Collection<int, Universe>
     * @ORM\ManyToMany(targetEntity=Universe::class, mappedBy="categories")
     */
    private Collection $universes;

    /**
     * @ORM\Column(name="lft", type="integer")
     * @Gedmo\TreeLeft
     */
    private int $left;

    /**
     * @ORM\Column(name="rgt", type="integer")
     * @Gedmo\TreeRight
     */
    private int $right;

    /**
     * @ORM\Column(name="lvl", type="integer")
     * @Gedmo\TreeLevel
     */
    private int $level;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class)
     * @Gedmo\TreeRoot
     */
    private ?Category $root = null;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="children")
     * @Gedmo\TreeParent
     */
    private ?Category $parent = null;

    /**
     * @var Collection<int, Category>
     * @ORM\OneToMany(targetEntity=Category::class, mappedBy="parent")
     */
    private Collection $children;

    /**
     * @ORM\ManyToOne(targetEntity=Product::class)
     */
    private ?Product $lastProduct = null;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->universes = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getUniverses(): Collection
    {
        return $this->universes;
    }

    public function getLeft(): int
    {
        return $this->left;
    }

    public function setLeft(int $left): Category
    {
        $this->left = $left;
        return $this;
    }

    public function getRight(): int
    {
        return $this->right;
    }

    public function setRight(int $right): Category
    {
        $this->right = $right;
        return $this;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): Category
    {
        $this->level = $level;
        return $this;
    }

    public function getRoot(): ?Category
    {
        return $this->root;
    }

    public function setRoot(?Category $root): Category
    {
        $this->root = $root;
        return $this;
    }

    public function getParent(): ?Category
    {
        return $this->parent;
    }

    public function setParent(?Category $parent): Category
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function getLastProduct(): ?Product
    {
        return $this->lastProduct;
    }
}
