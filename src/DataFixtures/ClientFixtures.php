<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\Member;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ClientFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create("fr_FR");

        for ($i = 1; $i <= 5; $i++) {
            /** @var Member $member */
            $member = $this->getReference(sprintf("member_%d", $i));

            for ($j = 1; $j <= 20; $j++) {
                $client = (new Client())
                    ->setMember($member)
                    ->setName($faker->company)
                    ->setCompanyNumber("443 061 841 00047")
                    ->setVatNumber("FR 64 443061841");
                $manager->persist($client);
                $this->addReference(sprintf("client_%d_%d", $i, $j), $client);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [MemberFixtures::class];
    }
}
