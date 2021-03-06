<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Key\Purchase;
use App\Entity\User\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class KeyFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        /** @var array<User> $users */
        $users = $manager->getRepository(User::class)->findAll();

        foreach ($users as $user) {
            $manager->persist((new Purchase())
                ->setMode(Purchase::MODE_BANK_WIRE)
                ->setAccount($user->getAccount())
                ->setPoints(5000)
                ->setState("accepted")
                ->prepare());
        }

        $manager->flush();
    }
}
