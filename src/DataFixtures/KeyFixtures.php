<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Company\Company;
use App\Entity\Company\Member;
use App\Entity\Key\Purchase;
use App\Entity\User\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class KeyFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [CustomerFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        /** @var array<Member> $members */
        $members = $manager->getRepository(Member::class)->findAll();

        foreach ($members as $member) {
            $purchase = (new Purchase())
                ->setMode(Purchase::MODE_BANK_WIRE)
                ->setAccount($member->getAccount())
                ->setPoints(5000)
                ->setState("accepted")
                ->prepare();
            $purchase->getWallet()->addTransaction($purchase);
            $manager->persist($purchase);
        }

        /** @var array<User> $users */
        $users = $manager->getRepository(User::class)->findAll();

        foreach ($users as $user) {
            $purchase = (new Purchase())
                ->setMode(Purchase::MODE_BANK_WIRE)
                ->setAccount($user->getAccount())
                ->setPoints(7000)
                ->setState("accepted")
                ->prepare();
            $purchase->getWallet()->addTransaction($purchase);
            $manager->persist($purchase);
        }

        $manager->flush();
    }
}
