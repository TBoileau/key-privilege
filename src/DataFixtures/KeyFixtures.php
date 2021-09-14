<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Address;
use App\Entity\Company\Company;
use App\Entity\Company\Member;
use App\Entity\Key\Purchase;
use App\Entity\User\Collaborator;
use App\Entity\User\Customer;
use App\Entity\User\Manager;
use App\Entity\User\SalesPerson;
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
        /** @var array<Member> $members */
        $members = $manager->getRepository(Member::class)->findAll();

        foreach ($members as $member) {
            /** @var Manager $user */
            $user = $member->getManagers()->first();

            $purchase = (new Purchase())
                ->setMode(Purchase::MODE_BANK_WIRE)
                ->setAccount($member->getAccount())
                ->setManager($user)
                ->setPoints(5000)
                ->setState("accepted")
                ->prepare();
            $purchase->setBillingAddress($member->getBillingAddress());
            /** @var Manager $user */
            $user = $member->getManagers()->first();
            $purchase->setDeliveryAddress($user->getDeliveryAddress());
            $purchase->getWallet()->addTransaction($purchase);
            $manager->persist($purchase);
        }

        /** @var array<Customer|SalesPerson|Collaborator|Manager> $users */
        $users = $manager->getRepository(User::class)->findAll();

        foreach ($users as $user) {
            $purchase = (new Purchase())
                ->setMode(Purchase::MODE_BANK_WIRE)
                ->setAccount($user->getAccount())
                ->setPoints(7000)
                ->setState("accepted")
                ->prepare();
            $purchase->setDeliveryAddress($user->getDeliveryAddress());

            if ($user instanceof Customer) {
                /** @var Address $address */
                $address = $user->getClient()->getMember()->getBillingAddress();
                $purchase->setBillingAddress($address);
            } elseif (in_array($user::class, [Collaborator::class, SalesPerson::class, Manager::class])) {
                /** @var Address $address */
                $address = $user->getMember()->getBillingAddress();
                $purchase->setBillingAddress($address);
            }

            $purchase->getWallet()->addTransaction($purchase);
            $manager->persist($purchase);
        }

        $manager->flush();
    }
}
