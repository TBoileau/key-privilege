<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Uuid;

class ForgottenPasswordTest extends WebTestCase
{
    public function testIfForgottenPasswordIsSuccessful(): void
    {
        $client = static::createClient();

        $crawler = $client->request(Request::METHOD_GET, "/oubli-mot-de-passe");

        $this->assertResponseIsSuccessful();

        $client->submit(
            $crawler->filter("form[name=forgotten_password]")->form([
                "forgotten_password[email]" => "user@email.com"
            ])
        );

        $this->assertEmailCount(1);

        /** @var UserRepository $userRepository */
        $userRepository = $client->getContainer()->get(UserRepository::class);

        /** @var User $user */
        $user = $userRepository->findBy(["email" => "user@email.com"]);

        $this->assertTrue(Uuid::isValid($user->getForgottenPasswordToken()));

        $client->followRedirect();

        $this->assertRouteSame("security_login");
    }
}
