<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Shop\Brand;
use App\Entity\Shop\Category;
use App\Entity\Shop\Product;
use App\Entity\Shop\Universe;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ShopFixtures extends Fixture
{

    /**
     * @var array<int, Universe>
     */
    private array $universes = [];

    private int $categoryId = 0;

    /**
     * @var array<int, Category>
     */
    private array $categories = [];

    /**
     * @var array<int, Brand>
     */
    private array $brands = [];

    private function createUniverses(ObjectManager $manager): void
    {
        for ($index = 1; $index <= 10; $index++) {
            $universe = (new Universe())->setId($index)->setName(sprintf("Univers %d", $index));
            $this->universes[$index] = $universe;
            $manager->persist($universe);
        }
    }

    private function createCategories(ObjectManager $manager, Category $parent): void
    {
        for ($index = 1; $index <= 5; $index++) {
            $manager->persist($category = (new Category())
                ->setId($this->categoryId)
                ->setParent($parent)
                ->setNumberOfProducts(10)
                ->setName(sprintf("Catégorie %d", $this->categoryId)));
            $this->categoryId++;

            for ($l = 1; $l <= 5; $l++) {
                $manager->persist($subCategory = (new Category())
                    ->setId($this->categoryId)
                    ->setParent($category)
                    ->setNumberOfProducts(10)
                    ->setName(sprintf("Catégorie %d", $this->categoryId)));
                $this->categories[] = $subCategory;
                $this->categoryId++;
            }
        }
    }

    private function createBrands(ObjectManager $manager): void
    {
        for ($index = 1; $index <= 400; $index++) {
            $brand = (new Brand())->setId($index)->setName(sprintf("Marque %d", $index));
            $this->brands[$index] = $brand;
            $manager->persist($brand);
        }
    }

    /**
     * @param EntityManagerInterface $manager
     */
    public function load(ObjectManager $manager): void
    {
        $this->createUniverses($manager);

        $manager->persist($category = (new Category())->setId(10000)->setName("Catégorie 1"));

        for ($j = 1; $j <= 20; $j++) {
            $manager->persist($categoryLevel1 = (new Category())
                ->setId($this->categoryId)
                ->setParent($category)
                ->setNumberOfProducts(10)
                ->setName(sprintf("Catégorie %d", $this->categoryId)));
            $this->categoryId++;

            shuffle($this->universes);

            /** @var Universe $universe */
            foreach (array_slice($this->universes, 0, 5) as $universe) {
                $universe->getCategories()->add($categoryLevel1);
            }

            $this->createCategories($manager, $categoryLevel1);

            $manager->flush();
        }

        $this->createBrands($manager);
        $manager->flush();

        $faker = Factory::create("fr_FR");

        for ($i = 1; $i <= 2000; $i++) {
            /** @var string $description */
            $description = $faker->sentences(5, true);

            $product = (new Product())
                ->setId($i)
                ->setName(sprintf("Produit %d", $i))
                ->setDescription($description)
                ->setCategory($this->categories[$i % count($this->categories)])
                ->setBrand($this->brands[($i % count($this->brands)) + 1])
                ->setReference(sprintf("REF_%d", $i))
                ->setAmount(intval(ceil(rand(10, 2000) / 5) * 5))
                ->setImage("image.png")
                ->setPurchasePrice(rand(10, 2000))
                ->setSalePrice(rand(10, 2000))
                ->setRetailPrice(rand(10, 2000))
                ->setVat(1)
                ->setUpdatedAt(new DateTimeImmutable());

            if ($i === 2000) {
                $product->setAmount(2000);
            }

            $manager->persist($product);

            if ($i > 2000 - count($this->categories)) {
                $this->categories[$i % count($this->categories)]->setLastProduct($product);
            }

            if ($i % 400 === 0) {
                $manager->flush();
            }
        }
    }
}
