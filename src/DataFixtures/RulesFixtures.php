<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Rules;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class RulesFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $manager->persist((new Rules())
            ->setPublishedAt(new DateTimeImmutable())
            ->setContent("<h1>Hello world!</h1>"));
        $manager->flush();
    }
}
