<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SuspendAccessTest extends WebTestCase
{
    public function testIfSuspendAccessIsSuccessful(): void
    {
        $client = static::createClient();

        /** @var UserRepository $userRepository */
        $userRepository = $client->getContainer()
            ->get("doctrine.orm.entity_manager")
            ->getRepository(User::class);

        /** @var User $user */
        $user = $userRepository->findOneBy(["email" => "user@email.com"]);

        $client->loginUser($user);

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        $client->request(
            Request::METHOD_GET,
            $urlGenerator->generate("access_suspend", ["id" => 10])
        );

        $client->submitForm("Suspendre", []);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        /** @var User $user */
        $user = $userRepository->find(10);

        $this->assertTrue($user->isSuspended());

        $client->followRedirect();

        $this->assertRouteSame("access_list");

        $client->request(
            Request::METHOD_GET,
            $urlGenerator->generate("access_active", ["id" => 10])
        );

        $client->submitForm("Activer", []);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        /** @var UserRepository $userRepository */
        $userRepository = $client->getContainer()
            ->get("doctrine.orm.entity_manager")
            ->getRepository(User::class);

        /** @var User $user */
        $user = $userRepository->find(10);

        $this->assertFalse($user->isSuspended());

        $client->followRedirect();

        $this->assertRouteSame("access_list");
    }
}
