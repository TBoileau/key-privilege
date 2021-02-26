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

class DeleteAccessTest extends WebTestCase
{
    public function testIfAccessListIsSuccessful(): void
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
            $urlGenerator->generate("access_delete", ["id" => $user->getId()])
        );

        $client->submitForm("Supprimer", []);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->getContainer()
            ->get("doctrine.orm.entity_manager")
            ->getFilters()
            ->disable("softdeleteable");

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var User $user */
        $user = $entityManager->find(User::class, $user->getId());

        $this->assertTrue($user->isDeleted());

        $client->followRedirect();

        $this->assertRouteSame("access_clients");
    }
}
