<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\User;
use App\Repository\UserRepository;
use Generator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Uid\Uuid;

class ForgottenPasswordTest extends WebTestCase
{
    public function testIfForgottenPasswordIsSuccessful(): void
    {
        $client = static::createClient();

        $crawler = $client->request(Request::METHOD_GET, "/mot-de-passe-oublie");

        $this->assertResponseIsSuccessful();

        $client->submit(
            $crawler->filter("form[name=forgotten_password]")->form([
                "forgotten_password[email]" => "user@email.com"
            ])
        );

        $this->assertEmailCount(1);

        /** @var UserRepository $userRepository */
        $userRepository = $client->getContainer()
            ->get("doctrine.orm.entity_manager")
            ->getRepository(User::class);

        /** @var User $user */
        $user = $userRepository->findOneBy(["email" => "user@email.com"]);

        $this->assertTrue(Uuid::isValid($user->getForgottenPasswordToken()));

        $client->followRedirect();

        $this->assertRouteSame("security_login");

        $crawler = $client->request(
            Request::METHOD_GET,
            "/reinitialisation-mot-de-passe/" . $user->getForgottenPasswordToken()
        );

        $this->assertResponseIsSuccessful();

        $client->submit(
            $crawler->filter("form[name=reset_password]")->form([
                "reset_password[plainPassword]" => "new_password"
            ])
        );

        /** @var User $user */
        $user = $userRepository->findOneBy(["email" => "user@email.com"]);

        /** @var UserPasswordEncoderInterface $userPasswordEncoder */
        $userPasswordEncoder = $client->getContainer()->get("security.password_encoder");

        $this->assertTrue($userPasswordEncoder->isPasswordValid($user, "new_password"));

        $this->assertNull($user->getForgottenPasswordToken());

        $crawler = $client->followRedirect();

        $this->assertRouteSame("security_login");

        $form = $crawler->filter("form[name=login]")->form([
            "email" => "user@email.com",
            "password" => "new_password"
        ]);

        $client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();

        $this->assertRouteSame("index");
    }

    public function testIfForgottenPasswordFormIsInvalid(): void
    {
        $client = static::createClient();

        $crawler = $client->request(Request::METHOD_GET, "/mot-de-passe-oublie");

        $this->assertResponseIsSuccessful();

        $client->submit(
            $crawler->filter("form[name=forgotten_password]")->form([
                "forgotten_password[email]" => "fail"
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertSelectorTextContains(
            ".form-error-message",
            "Cette valeur n'est pas une adresse email valide."
        );
    }

    public function testIfForgottenPasswordCsrfTokenIsInvalid(): void
    {
        $client = static::createClient();

        $crawler = $client->request(Request::METHOD_GET, "/mot-de-passe-oublie");

        $this->assertResponseIsSuccessful();

        $client->submit(
            $crawler->filter("form[name=forgotten_password]")->form([
                "forgotten_password[email]" => "user@email.com",
                "forgotten_password[_token]" => "fail"
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertSelectorTextContains(
            ".form-error-message",
            "Le jeton CSRF est invalide. Veuillez renvoyer le formulaire."
        );
    }



    public function testIfResetPasswordCsrfTokenIsInvalid(): void
    {
        $client = static::createClient();

        /** @var UserRepository $userRepository */
        $userRepository = $client->getContainer()
            ->get("doctrine.orm.entity_manager")
            ->getRepository(User::class);

        /** @var User $user */
        $user = $userRepository->findOneBy(["email" => "user+forgotten+password@email.com"]);

        $crawler = $client->request(
            Request::METHOD_GET,
            "/reinitialisation-mot-de-passe/" . $user->getForgottenPasswordToken()
        );


        $this->assertResponseIsSuccessful();

        $client->submit(
            $crawler->filter("form[name=reset_password]")->form([
                "reset_password[plainPassword]" => "new_password",
                "reset_password[_token]" => "fail"
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertSelectorTextContains(
            ".form-error-message",
            "Le jeton CSRF est invalide. Veuillez renvoyer le formulaire."
        );
    }

    /**
     * @dataProvider provideBadDataForResetPassword
     */
    public function testIfResetPasswordFormIsInvalid(array $formData, string $errorMessage): void
    {
        $client = static::createClient();

        /** @var UserRepository $userRepository */
        $userRepository = $client->getContainer()
            ->get("doctrine.orm.entity_manager")
            ->getRepository(User::class);

        /** @var User $user */
        $user = $userRepository->findOneBy(["email" => "user+forgotten+password@email.com"]);

        $crawler = $client->request(
            Request::METHOD_GET,
            "/reinitialisation-mot-de-passe/" . $user->getForgottenPasswordToken()
        );

        $this->assertResponseIsSuccessful();

        $client->submit(
            $crawler->filter("form[name=reset_password]")->form($formData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertSelectorTextContains(
            ".form-error-message",
            $errorMessage
        );
    }

    public function provideBadDataForResetPassword(): Generator
    {
        yield [
            ["reset_password[plainPassword]" => "fail"],
            "Cette chaîne est trop courte. Elle doit avoir au minimum 8 caractères."
        ];

        yield [
            ["reset_password[plainPassword]" => ""],
            "Cette valeur ne doit pas être vide."
        ];
    }
}
