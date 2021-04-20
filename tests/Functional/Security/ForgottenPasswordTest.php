<?php

declare(strict_types=1);

namespace App\Tests\Functional\Security;

use App\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;
use Generator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Uid\Uuid;

class ForgottenPasswordTest extends WebTestCase
{
    public function testIfForgottenPasswordIsSuccessful(): void
    {
        $client = static::createClient();

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        $crawler = $client->request(
            Request::METHOD_GET,
            $urlGenerator->generate("security_forgotten_password")
        );

        $this->assertResponseIsSuccessful();

        $client->submit(
            $crawler->filter("form[name=forgotten_password]")->form([
                "forgotten_password[username]" => "user1"
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $this->assertEmailCount(1);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var User $user */
        $user = $entityManager->find(User::class, 1);

        $this->assertTrue(Uuid::isValid($user->getForgottenPasswordToken()));

        $client->followRedirect();

        $this->assertRouteSame("security_login");

        $crawler = $client->request(
            Request::METHOD_GET,
            $urlGenerator->generate("security_reset_password", [
                "forgottenPasswordToken" => $user->getForgottenPasswordToken()
            ])
        );

        $this->assertResponseIsSuccessful();

        $client->submit(
            $crawler->filter("form[name=reset_password]")->form([
                "reset_password[plainPassword]" => "new_password"
            ])
        );

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var User $user */
        $user = $entityManager->find(User::class, 1);

        /** @var UserPasswordEncoderInterface $userPasswordEncoder */
        $userPasswordEncoder = $client->getContainer()->get("security.password_encoder");

        $this->assertTrue($userPasswordEncoder->isPasswordValid($user, "new_password"));

        $this->assertNull($user->getForgottenPasswordToken());

        $crawler = $client->followRedirect();

        $this->assertRouteSame("security_login");

        $form = $crawler->filter("form[name=login]")->form([
            "username" => $user->getUsername(),
            "password" => "new_password"
        ]);

        $client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
    }

    public function testIfForgottenPasswordCsrfTokenIsInvalid(): void
    {
        $client = static::createClient();

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        $crawler = $client->request(
            Request::METHOD_GET,
            $urlGenerator->generate("security_forgotten_password")
        );

        $this->assertResponseIsSuccessful();

        $client->submit(
            $crawler->filter("form[name=forgotten_password]")->form([
                "forgotten_password[username]" => "user1",
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

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var User $user */
        $user = $entityManager->find(User::class, 1);

        $user->setForgottenPasswordToken("token");

        $entityManager->flush();

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        $crawler = $client->request(
            Request::METHOD_GET,
            $urlGenerator->generate("security_reset_password", [
                "forgottenPasswordToken" => $user->getForgottenPasswordToken()
            ])
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

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var User $user */
        $user = $entityManager->find(User::class, 1);

        $user->setForgottenPasswordToken("token");

        $entityManager->flush();

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        $crawler = $client->request(
            Request::METHOD_GET,
            $urlGenerator->generate("security_reset_password", [
                "forgottenPasswordToken" => $user->getForgottenPasswordToken()
            ])
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
