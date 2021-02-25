<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Role;
use App\Store\RoleStore;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class RoleFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $administratorMono = (new Role())
            ->setName("Administrateur mono-adhérent")
            ->setRoles([
                RoleStore::ROLE_SHOP,
                RoleStore::ROLE_ORDER,
                RoleStore::ROLE_CLIENTS_COMPANIES,
                RoleStore::ROLE_CLIENT_ACCESS,
                RoleStore::ROLE_MEMBER_ACCESS,
                RoleStore::ROLE_MEMBERS_COMPANIES
            ]);
        $manager->persist($administratorMono);
        $this->addReference("administrator_mono", $administratorMono);

        $administratorMulti = (new Role())
            ->setName("Administrateur multi-adhérents")
            ->setRoles([
                RoleStore::ROLE_SHOP,
                RoleStore::ROLE_ORDER,
                RoleStore::ROLE_CLIENTS_COMPANIES,
                RoleStore::ROLE_CLIENT_ACCESS,
                RoleStore::ROLE_MEMBER_ACCESS,
                RoleStore::ROLE_MEMBERS_COMPANIES,
                RoleStore::ROLE_MULTI_MEMBERS
            ]);
        $manager->persist($administratorMulti);
        $this->addReference("administrator_multi", $administratorMulti);

        $salesPerson = (new Role())
            ->setName("Commercial")
            ->setRoles([
                RoleStore::ROLE_SHOP,
                RoleStore::ROLE_CLIENT_ACCESS,
                RoleStore::ROLE_CLIENTS_COMPANIES,
                RoleStore::ROLE_ORDER
            ]);
        $manager->persist($salesPerson);
        $this->addReference("sales_person", $salesPerson);

        $collaborator = (new Role())
            ->setName("Collaborateur")
            ->setRoles([
                RoleStore::ROLE_SHOP,
                RoleStore::ROLE_ORDER
            ]);
        $manager->persist($collaborator);
        $this->addReference("collaborator", $collaborator);

        $customer = (new Role())
            ->setName("Client")
            ->setRoles([
                RoleStore::ROLE_SHOP,
                RoleStore::ROLE_ORDER
            ]);
        $manager->persist($customer);
        $this->addReference("customer", $customer);

        $manager->flush();
    }
}
