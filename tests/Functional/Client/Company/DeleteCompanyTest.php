<?php

declare(strict_types=1);

namespace App\Tests\Functional\Client\Company;

use App\Entity\Company\Client;
use App\Entity\User\Manager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DeleteCompanyTest extends WebTestCase
{
    public function testIfDeleteCompanyIsSuccessful(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Manager $manager */
        $manager = $entityManager->find(Manager::class, 1);

        /** @var Client $clientCompany */
        $clientCompany = $entityManager->find(Client::class, 16);

        $client->loginUser($manager);

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        $client->request(
            Request::METHOD_GET,
            $urlGenerator->generate("client_company_delete", ["id" => $clientCompany->getId()])
        );

        $client->submitForm("Supprimer", []);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->getContainer()
            ->get("doctrine.orm.entity_manager")
            ->getFilters()
            ->disable("softdeleteable");

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Client $clientCompany */
        $clientCompany = $entityManager->find(Client::class, $clientCompany->getId());

        $this->assertTrue($clientCompany->isDeleted());

        $client->followRedirect();

        $this->assertRouteSame("client_company_list");
    }
}
