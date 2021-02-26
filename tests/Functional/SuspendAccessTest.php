<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Manager;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SuspendAccessTest extends WebTestCase
{
    public function testIfSuspendAccessIsSuccessful(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Manager $manager */
        $manager = $entityManager->find(Manager::class, 1);

        /** @var User $user */
        $user = $entityManager->find(User::class, 16);

        $client->loginUser($manager);

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        $client->request(
            Request::METHOD_GET,
            $urlGenerator->generate("access_suspend", ["id" => $user->getId()])
        );

        $client->submitForm("Suspendre", []);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var User $user */
        $user = $entityManager->find(User::class, $user->getId());

        $this->assertTrue($user->isSuspended());

        $client->followRedirect();

        $this->assertRouteSame("access_clients");

        $client->request(
            Request::METHOD_GET,
            $urlGenerator->generate("access_active", ["id" => $user->getId()])
        );

        $client->submitForm("Activer", []);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var User $user */
        $user = $entityManager->find(User::class, $user->getId());

        $this->assertFalse($user->isSuspended());

        $client->followRedirect();

        $this->assertRouteSame("access_clients");
    }
}
