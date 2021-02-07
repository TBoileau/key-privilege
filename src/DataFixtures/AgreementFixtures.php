<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Rules;
use App\Entity\RulesAgreement;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class AgreementFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [UserFixtures::class, RulesFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        /** @var User $user */
        $user = $manager->getRepository(User::class)->findOneBy(["email" => "user@email.com"]);

        /** @var Rules $rules */
        $rules = $manager->getRepository(Rules::class)->findOneBy([]);

        $user->acceptRules($rules);

        /** @var User $user */
        $refusedRulesUser = $manager->getRepository(User::class)
            ->findOneBy(["email" => "user+refused+rules@email.com"]);

        $refusedRulesUser->refuseRules($rules);

        $manager->flush();
    }
}
