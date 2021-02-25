<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Member;
use App\Entity\Organization;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class MemberFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create("fr_FR");

        /** @var Organization $organization */
        $organization = $this->getReference("organization");
        for ($i = 1; $i <= 5; $i++) {
            $member = (new Member())
                ->setOrganization($organization)
                ->setName($faker->company)
                ->setCompanyNumber("443 061 841 00047")
                ->setVatNumber("FR 64 443061841");
            $manager->persist($member);
            $this->addReference(sprintf("member_%d", $i), $member);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [OrganizationFixtures::class];
    }
}