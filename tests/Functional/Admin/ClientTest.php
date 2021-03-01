<?php

declare(strict_types=1);

namespace App\Tests\Functional\Admin;

use App\Controller\Admin\ClientCrudController;
use App\Entity\Administrator;
use App\Entity\Company\Client;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ClientTest extends WebTestCase
{
    public function testIfClientManageIsSuccessful(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var AdminUrlGenerator $urlGenerator */
        $adminUrlGenerator = $client->getContainer()->get(AdminUrlGenerator::class);

        $admin = $entityManager->find(Administrator::class, 1);

        $client->loginUser($admin, "admin");

        $client->request(
            "GET",
            $adminUrlGenerator
                ->setController(ClientCrudController::class)
                ->setAction(Action::INDEX)
                ->generateUrl()
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $client->request(
            "GET",
            $adminUrlGenerator
                ->setController(ClientCrudController::class)
                ->setAction(Action::NEW)
                ->generateUrl()
        );

        $this->assertResponseIsSuccessful();

        $client->submitForm("Créer", [
            "Client[name]" => "Raison sociale",
            "Client[companyNumber]" => "44306184100047",
            "Client[member]" => 2,
            "Client[salesPerson]" => 6
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->request(
            "GET",
            $adminUrlGenerator
                ->setController(ClientCrudController::class)
                ->setAction(Action::EDIT)
                ->setEntityId(7)
                ->generateUrl()
        );

        $this->assertResponseIsSuccessful();

        $client->submitForm("Sauvegarder les modifications", [
            "Client[name]" => "Raison sociale",
            "Client[companyNumber]" => "42878504200105",
            "Client[member]" => 2,
            "Client[salesPerson]" => 6
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        /** @var Client $clientCompany */
        $clientCompany = $entityManager->getRepository(Client::class)->findOneByName("Raison sociale");

        $client->request(
            "GET",
            $adminUrlGenerator
                ->setController(ClientCrudController::class)
                ->setAction(Action::DELETE)
                ->setEntityId($clientCompany->getId())
                ->generateUrl()
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->request(
            "GET",
            $adminUrlGenerator
                ->setController(ClientCrudController::class)
                ->setAction(Action::DETAIL)
                ->setEntityId(7)
                ->generateUrl()
        );

        $this->assertResponseIsSuccessful();
    }
}
