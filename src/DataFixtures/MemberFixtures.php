<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Company\Member;
use App\Entity\Company\Organization;
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
            /** @var Member $member */
            $member = (new Member())
                ->setOrganization($organization)
                ->setName($faker->company)
                ->setCompanyNumber("44306184100047");
            $member->getBillingAddress()
                ->setProfessional(true)
                ->setFirstName("John")
                ->setLastName("Doe")
                ->setCompanyName($member->getName())
                ->setLocality("Paris")
                ->setZipCode("75000")
                ->setEmail("email@email.com")
                ->setPhone("0123456789")
                ->setStreetAddress("1 rue de la mairie");
            $member->getBillingAddresses()->add($member->getBillingAddress());
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
