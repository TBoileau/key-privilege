<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\Company;
use App\Entity\Manager;
use App\Entity\Member;
use App\Entity\Role;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
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
        $this->autoIncrement = 16;
    }

    public function load(ObjectManager $manager): void
    {
        /** @var array<Client> $clients */
        $clients = $manager->getRepository(Client::class)->findAll();

        foreach ($clients as $client) {
            $manager->persist($this->createUser()->setClient($client));
        }

        $manager->flush();
    }

    private function createUser(): User
    {
        $user = (new User())
            ->setFirstName($this->faker->firstName)
            ->setLastName($this->faker->lastName)
            ->setEmail(sprintf("user+%d@email.com", $this->autoIncrement));

        $user->setPassword($this->userPasswordEncoder->encodePassword($user, "password"));

        $this->autoIncrement++;

        return $user;
    }

    public function getDependencies(): array
    {
        return [ClientFixtures::class];
    }
}
