<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Question;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class FaqFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create("fr_FR");

        for ($i = 1; $i <= 10; $i++) {
            $manager->persist((new Question())->setName($faker->sentence())->setAnswer($faker->sentence()));
        }

        $manager->flush();
    }
}
