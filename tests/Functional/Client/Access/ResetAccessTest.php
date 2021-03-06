<?php

declare(strict_types=1);

namespace App\Tests\Functional\Client\Access;

use App\Entity\User\Manager;
use App\Entity\User\Customer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ResetAccessTest extends WebTestCase
{
    public function testIfResetAccessIsSuccessful(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Manager $manager */
        $manager = $entityManager->find(Manager::class, 1);

        /** @var Customer $user */
        $user = $entityManager->find(Customer::class, 16);

        $oldPassword = $user->getPassword();

        $client->loginUser($manager);

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        $client->request(Request::METHOD_GET, $urlGenerator->generate("client_access_reset", ["id" => $user->getId()]));

        $client->submitForm("Réinitialiser", []);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $this->assertEmailCount(1);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Customer $user */
        $user = $entityManager->find(Customer::class, $user->getId());

        $this->assertNotEquals($oldPassword, $user->getPassword());
    }
}
