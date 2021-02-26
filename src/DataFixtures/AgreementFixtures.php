<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\AbstractUser;
use App\Entity\Rules;
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
        /** @var Rules $rules */
        $rules = $this->getReference("rules");

        /** @var AbstractUser[] $users */
        $users = $manager->getRepository(AbstractUser::class)->findAll();

        foreach ($users as $user) {
            if ($user->getId() % 3 === 0) {
                $user->refuseRules($rules);
            } else {
                $user->acceptRules($rules);
            }
        }

        $manager->flush();
    }
}
