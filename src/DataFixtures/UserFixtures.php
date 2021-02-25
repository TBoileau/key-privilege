<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\Company;
use App\Entity\Member;
use App\Entity\Role;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    private UserPasswordEncoderInterface $userPasswordEncoder;

    private Generator $faker;

    private int $autoIncrement;

    public function __construct(UserPasswordEncoderInterface $userPasswordEncoder)
    {
        $this->userPasswordEncoder = $userPasswordEncoder;
        $this->faker = Factory::create("fr_FR");
        $this->autoIncrement = 1;
    }

    public function load(ObjectManager $manager): void
    {
        /** @var Role $administratorMono */
        $administratorMono = $this->getReference("administrator_mono");

        /** @var Role $administratorMulti */
        $administratorMulti = $this->getReference("administrator_mono");

        /** @var Role $salesPerson */
        $salesPerson = $this->getReference("sales_person");

        /** @var Role $collaborator */
        $collaborator = $this->getReference("collaborator");

        /** @var Role $customer */
        $customer = $this->getReference("customer");

        /** @var Member $member */
        $member = $this->getReference("member_1");

        $administrator = $this->createUser($administratorMulti, $member, "Prénom", "Nom");
        $manager->persist($administrator);

        for ($i = 1; $i <= 5; $i++) {
            /** @var Member $member */
            $member = $this->getReference(sprintf("member_%d", $i));

            if ($i > 1) {
                $administrator->getCompanies()->add($member);
            }


            $manager->persist($this->createUser($administratorMono, $member));
            $salesPersonUser = $this->createUser($salesPerson, $member);
            $manager->persist($salesPersonUser);
            $manager->persist($this->createUser($collaborator, $member));

            for ($j = 1; $j <= 20; $j++) {
                /** @var Client $client */
                $client = $this->getReference(sprintf("client_%d_%d", $i, $j));
                $client->setUser($salesPersonUser);
                $manager->persist($this->createUser($customer, $client));
            }

            $manager->flush();
        }
    }

    private function createUser(Role $role, Company $company, ?string $firstName = null, ?string $lastName = null): User
    {
        $user = (new User())
            ->setRole($role)
            ->setCompany($company)
            ->setFirstName($firstName ?? $this->faker->firstName)
            ->setLastName($lastName ?? $this->faker->lastName)
            ->setEmail(sprintf("user+%d@email.com", $this->autoIncrement));

        $user->setPassword($this->userPasswordEncoder->encodePassword($user, "password"));

        $this->autoIncrement++;

        return $user;
    }

    public function getDependencies(): array
    {
        return [
            ClientFixtures::class,
            RoleFixtures::class
        ];
    }
}
