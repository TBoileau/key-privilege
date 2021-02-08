<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class EditPasswordTest extends WebTestCase
{
    public function testIfEditPasswordIsSuccessful(): void
    {
        $client = static::createClient();

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        /** @var UserRepository $userRepository */
        $userRepository = $client->getContainer()
            ->get("doctrine.orm.entity_manager")
            ->getRepository(User::class);

        /** @var User $user */
        $user = $userRepository->findOneBy(["email" => "user@email.com"]);

        $client->loginUser($user);

        $crawler = $client->request(Request::METHOD_GET, $urlGenerator->generate("account_edit_password"));

        $this->assertResponseIsSuccessful();

        $client->submit($crawler->filter("form[name=edit_password]")->form([
            "edit_password[currentPassword]" => "password",
            "edit_password[plainPassword]" => "new_password"
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        /** @var User $user */
        $user = $userRepository->findOneBy(["email" => "user@email.com"]);

        /** @var UserPasswordEncoderInterface $userPasswordEncoder */
        $userPasswordEncoder = $client->getContainer()->get("security.password_encoder");

        $this->assertTrue($userPasswordEncoder->isPasswordValid($user, "new_password"));

        $this->assertNull($user->getForgottenPasswordToken());

        $client->followRedirect();

        $this->assertRouteSame("account_edit_password");
    }
}
