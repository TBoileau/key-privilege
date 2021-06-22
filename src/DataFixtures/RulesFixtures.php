<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Rules;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class RulesFixtures extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        $rules = (new Rules())
            ->setPublishedAt(new DateTimeImmutable())
            ->setContent("<h1>Hello world!</h1>");
        $manager->persist($rules);
        $this->addReference("rules", $rules);
        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['prod'];
    }
}
