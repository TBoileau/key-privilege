<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User\Collaborator;
use App\Entity\Company\Member;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class CollaboratorFixtures extends Fixture implements DependentFixtureInterface
{
    private UserPasswordEncoderInterface $userPasswordEncoder;

    private Generator $faker;

    private int $autoIncrement;

    public function __construct(UserPasswordEncoderInterface $userPasswordEncoder)
    {
        $this->userPasswordEncoder = $userPasswordEncoder;
        $this->faker = Factory::create("fr_FR");
        $this->autoIncrement = 11;
    }

    public function load(ObjectManager $manager): void
    {
        /** @var array<Member> $members */
        $members = $manager->getRepository(Member::class)->findAll();

        foreach ($members as $member) {
            $manager->persist($this->createUser()->setMember($member)->setPhone("0123456789"));
        }

        $manager->flush();
    }

    private function createUser(): Collaborator
    {
        /** @var Collaborator $user */
        $user = (new Collaborator())
            ->setUsername(sprintf("user+%d", $this->autoIncrement))
            ->setFirstName($this->faker->firstName)
            ->setLastName($this->faker->lastName)
            ->setEmail(sprintf("user+%d@email.com", $this->autoIncrement));

        $user->setPassword($this->userPasswordEncoder->encodePassword($user, "password"));

        $this->autoIncrement++;

        return $user;
    }

    public function getDependencies(): array
    {
        return [SalesPersonFixtures::class];
    }
}
