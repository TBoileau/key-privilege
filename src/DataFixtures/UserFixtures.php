<?php

declare(strict_types=1);

namespace App\DataFixtures;

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
        }

        $manager->flush();
    }
}
