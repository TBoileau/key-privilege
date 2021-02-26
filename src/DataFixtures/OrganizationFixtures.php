<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Company\Organization;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class OrganizationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create("fr_FR");

        $organization = (new Organization())
            ->setName($faker->company)
            ->setCompanyNumber("443 061 841 00047")
            ->setVatNumber("FR 64 443061841");
        $manager->persist($organization);
        $this->addReference("organization", $organization);
        $manager->flush();
    }
}
