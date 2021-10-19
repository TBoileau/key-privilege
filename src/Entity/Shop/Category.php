<?php

namespace App\Entity\Shop;

use App\Repository\Shop\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass=CategoryRepository::class)
 * @Gedmo\Tree(type="nested")
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
     * @ORM\Column(unique=true)
     * @Gedmo\Slug(fields={"name"}, unique=true)
     */
    private string $slug;

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

    /**
     * @ORM\Column(type="integer")
     */
    private int $numberOfProducts = 0;

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

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }

    /**
     * @return Collection<int, Universe>
     */
    public function getUniverses(): Collection
    {
        return $this->universes;
    }

    public function getLeft(): int
    {
        return $this->left;
    }

    public function setLeft(int $left): self
    {
        $this->left = $left;
        return $this;
    }

    public function getRight(): int
    {
        return $this->right;
    }

    public function setRight(int $right): self
    {
        $this->right = $right;
        return $this;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): self
    {
        $this->level = $level;
        return $this;
    }

    public function getParent(): ?Category
    {
        return $this->parent;
    }

    public function setParent(?Category $parent): self
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

    public function setLastProduct(?Product $lastProduct): self
    {
        $this->lastProduct = $lastProduct;
        return $this;
    }

    public function getNumberOfProducts(): int
    {
        return $this->numberOfProducts;
    }

    public function setNumberOfProducts(int $numberOfProducts): self
    {
        $this->numberOfProducts = $numberOfProducts;
        return $this;
    }
}
