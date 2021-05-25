<?php

declare(strict_types=1);

namespace App\Tests\Functional\Security;

use App\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class LoginTest extends WebTestCase
{
    public function testIfLoginIsSuccessful(): void
    {
        $client = static::createClient();

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        $crawler = $client->request("GET", $urlGenerator->generate("security_login"));

        $form = $crawler->filter("form[name=login]")->form([
            "username" => "user1",
            "password" => "password"
        ]);

        $client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();

        $this->assertRouteSame("home");
    }

    public function testIfLoginFailedWhenUserIsDeleted(): void
    {
        $client = static::createClient();

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var User $user */
        $user = $entityManager->find(User::class, 1);

        $user->setDeletedAt(new \DateTime());

        $entityManager->flush();

        $crawler = $client->request("GET", $urlGenerator->generate("security_login"));

        $form = $crawler->filter("form[name=login]")->form([
            "username" => "user1",
            "password" => "password"
        ]);

        $client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();

        $this->assertRouteSame("security_login");

        $this->assertSelectorTextContains("form[name=login] > .alert-danger", "Identifiants invalides.");
    }

    public function testIfLoginFailedWhenUserIsSuspended(): void
    {
        $client = static::createClient();

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var User $user */
        $user = $entityManager->find(User::class, 1);

        $user->setSuspended(true);

        $entityManager->flush();

        $crawler = $client->request("GET", $urlGenerator->generate("security_login"));

        $form = $crawler->filter("form[name=login]")->form([
            "username" => "user1",
            "password" => "password"
        ]);

        $client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();

        $this->assertRouteSame("security_login");

        $this->assertSelectorTextContains("form[name=login] > .alert-danger", "Identifiants invalides.");
    }

    public function testIfLoginFailedWhenEmailDoesNotExist(): void
    {
        $client = static::createClient();

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        $crawler = $client->request("GET", $urlGenerator->generate("security_login"));

        $form = $crawler->filter("form[name=login]")->form([
            "username" => "fail",
            "password" => "password"
        ]);

        $client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();

        $this->assertRouteSame("security_login");

        $this->assertSelectorTextContains("form[name=login] > .alert-danger", "Identifiants invalides.");
    }

    public function testIfLoginFailedWhenPasswordIsWrong(): void
    {
        $client = static::createClient();

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        $crawler = $client->request("GET", $urlGenerator->generate("security_login"));

        $form = $crawler->filter("form[name=login]")->form([
            "username" => "user",
            "password" => "fail"
        ]);

        $client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();

        $this->assertRouteSame("security_login");

        $this->assertSelectorTextContains("form[name=login] > .alert-danger", "Identifiants invalides.");
    }

    public function testIfLoginFailedWhenCsrfIsWrong(): void
    {
        $client = static::createClient();

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        $crawler = $client->request("GET", $urlGenerator->generate("security_login"));

        $form = $crawler->filter("form[name=login]")->form([
            "username" => "user",
            "password" => "password",
            "_csrf_token" => "fail"
        ]);

        $client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();

        $this->assertRouteSame("security_login");

        $this->assertSelectorTextContains("form[name=login] > .alert-danger", "Jeton CSRF invalide.");
    }
}
