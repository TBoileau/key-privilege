<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Administrator;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class AdministratorFixtures extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        $admin = new Administrator();
        $admin->setPlainPassword("password");
        $admin->setEmail("admin@email.com");
        $admin->setFirstName("John");
        $admin->setLastName("Doe");
        $manager->persist($admin);
        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['prod'];
    }
}
