<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Address;
use App\Entity\User\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [CustomerFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        /** @var array<int, User> $users */
        $users = $manager->getRepository(User::class)->findAll();

        foreach ($users as $user) {
            $user->setForgottenPasswordToken($user->getUsername());

            $user->setDeliveryAddress(new Address());
            $user->getDeliveryAddress()
                ->setFirstName("John")
                ->setLastName("Doe")
                ->setCompanyName('COmpany name')
                ->setProfessional(true)
                ->setLocality("Paris")
                ->setZipCode("75000")
                ->setEmail("email@email.com")
                ->setPhone("0123456789")
                ->setStreetAddress("1 rue de la mairie");
            $user->getDeliveryAddresses()->add($user->getDeliveryAddress());
        }

        $manager->flush();
    }
}
