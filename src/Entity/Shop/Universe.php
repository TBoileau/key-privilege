<?php

namespace App\Entity\Shop;

use App\Repository\Shop\UniverseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass=UniverseRepository::class)
 */
class Universe
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
     * @var Collection<int, Category>
     * @ORM\ManyToMany(targetEntity=Category::class, inversedBy="universes")
     * @ORM\JoinTable(name="universe_categories")
     */
    private Collection $categories;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
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

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
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

    public function getNumberOfProducts(): int
    {
        return array_sum(
            $this->categories->map(fn (Category $category) => $category->getNumberOfProducts())->toArray()
        );
    }
}
