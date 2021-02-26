<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\Member;
use App\Entity\SalesPerson;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ClientFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create("fr_FR");

        /** @var array<SalesPerson> $salesPersons */
        $salesPersons = $manager->getRepository(SalesPerson::class)->findAll();

        foreach ($salesPersons as $salesPerson) {
            for ($j = 1; $j <= 20; $j++) {
                $client = (new Client())
                    ->setMember($salesPerson->getMember())
                    ->setSalesPerson($salesPerson)
                    ->setName($faker->company)
                    ->setCompanyNumber("443 061 841 00047")
                    ->setVatNumber("FR 64 443061841");
                $manager->persist($client);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [SalesPersonFixtures::class];
    }
}
