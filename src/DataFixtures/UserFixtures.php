<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Uid\Uuid;

class UserFixtures extends Fixture
{
    private UserPasswordEncoderInterface $userPasswordEncoder;

    public function __construct(UserPasswordEncoderInterface $userPasswordEncoder)
    {
        $this->userPasswordEncoder = $userPasswordEncoder;
    }

    public function load(ObjectManager $manager): void
    {
        $user = (new User())->setEmail("user@email.com");
        $manager->persist($user->setPassword($this->userPasswordEncoder->encodePassword($user, "password")));

        $refusedRulesUser = (new User())->setEmail("user+refused+rules@email.com");
        $manager->persist(
            $refusedRulesUser->setPassword(
                $this->userPasswordEncoder->encodePassword(
                    $refusedRulesUser,
                    "password"
                )
            )
        );

        $forgottenPasswordUser = (new User())
            ->setEmail("user+forgotten+password@email.com")
            ->setForgottenPasswordToken((string) Uuid::v4());
        $manager->persist(
            $forgottenPasswordUser
                ->setPassword(
                    $this->userPasswordEncoder->encodePassword(
                        $forgottenPasswordUser,
                        "password"
                    )
                )
        );

        $manager->flush();
    }
}
