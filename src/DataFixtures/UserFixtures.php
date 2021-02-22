<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use DateTime;
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
        $user = (new User())
            ->setFirstName("Arthur")
            ->setLastName("Dupont")
            ->setEmail("user@email.com");
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

        $suspendUser = (new User())
            ->setFirstName("Jean")
            ->setLastName("Dupont")
            ->setEmail("user+suspend@email.com")
            ->setSuspended(true);
        $manager->persist(
            $suspendUser
                ->setPassword(
                    $this->userPasswordEncoder->encodePassword(
                        $suspendUser,
                        "password"
                    )
                )
        );

        $deletedUser = (new User())
            ->setFirstName("Jean")
            ->setLastName("Dupont")
            ->setEmail("user+deleted@email.com")
            ->setDeletedAt(new DateTime());
        $manager->persist(
            $deletedUser
                ->setPassword(
                    $this->userPasswordEncoder->encodePassword(
                        $deletedUser,
                        "password"
                    )
                )
        );

        $forgottenPasswordUser = (new User())
            ->setFirstName("Jean")
            ->setLastName("Dupont")
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

        for ($i = 1; $i <= 20; $i++) {
            $user = (new User())
                ->setFirstName("Jean")
                ->setLastName("Dupont")
                ->setEmail(sprintf("user+%d@email.com", $i))
                ->setForgottenPasswordToken((string) Uuid::v4());
            $manager->persist(
                $user
                    ->setPassword(
                        $this->userPasswordEncoder->encodePassword(
                            $user,
                            "password"
                        )
                    )
            );
        }

        $manager->flush();
    }
}
