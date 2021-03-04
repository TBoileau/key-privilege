<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Shop\Brand;
use App\Entity\Shop\Category;
use App\Entity\Shop\Product;
use App\Entity\Shop\Universe;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ShopFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        /** @var array<int, Universe> $universes */
        $universes = [];

        for ($i = 1; $i <= 10; $i++) {
            $universe = (new Universe())->setId($i)->setName(sprintf("Univers %d", $i));
            $universes[$i] = $universe;
            $manager->persist($universe);
        }

        $categoryId = 0;

        /** @var array<int, Category> $categories */
        $categories = [];

        $manager->persist($category = (new Category())->setId(10000)->setName("Catégorie 1"));
        $categoryId++;

        for ($j = 1; $j <= 20; $j++) {
            $manager->persist($category = (new Category())
                ->setId($categoryId)
                ->setParent($category)
                ->setName(sprintf("Catégorie %d", $categoryId)));
            $categories[$categoryId] = $category;
            $categoryId++;

            shuffle($universes);

            /** @var Universe $universe */
            foreach (array_slice($universes, 0, 5) as $universe) {
                $universe->getCategories()->add($category);
            }

            for ($k = 1; $k <= 5; $k++) {
                $manager->persist($category = (new Category())
                    ->setId($categoryId)
                    ->setParent($category)
                    ->setName(sprintf("Catégorie %d", $categoryId)));
                $categories[$categoryId] = $category;
                $categoryId++;

                for ($l = 1; $l <= 5; $l++) {
                    $manager->persist($category = (new Category())
                        ->setId($categoryId)
                        ->setParent($category)
                        ->setName(sprintf("Catégorie %d", $categoryId)));
                    $categories[$categoryId] = $category;
                    $categoryId++;
                }
            }

            $manager->flush();
        }

        /** @var array<int, Brand> $brands */
        $brands = [];

        for ($i = 1; $i <= 400; $i++) {
            $brand = (new Brand())->setId($i)->setName(sprintf("Marque %d", $i));
            $brands[$i] = $brand;
            $manager->persist($brand);
        }

        $manager->flush();

        $faker = Factory::create("fr_FR");

        for ($i = 1; $i <= 2000; $i++) {
            $manager->persist(
                $product = (new Product())
                    ->setId($i)
                    ->setName(sprintf("Produit %d", $i))
                    ->setDescription($faker->sentences(5, true))
                    ->setCategory($categories[($i % count($categories)) + 1])
                    ->setBrand($brands[($i % count($brands)) + 1])
                    ->setReference(sprintf("REF_%d", $i))
                    ->setAmount(intval(ceil(rand(10, 2000) / 5) * 5))
                    ->setImage("uploads/shop/products/image.png")
                    ->setUpdatedAt(new DateTimeImmutable())
            );

            if ($i > 2000 - count($categories)) {
                $categories[($i % count($categories)) + 1]->setLastProduct($product);
            }

            if ($i % 400 === 0) {
                $manager->flush();
            }
        }
    }
}
