<?php

declare(strict_types=1);

namespace App\Tests\Functional\Admin;

use App\Controller\Admin\SalesPersonCrudController;
use App\Entity\Administrator;
use App\Entity\User\SalesPerson;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class SalesPersonTest extends WebTestCase
{
    public function testIfSalesPersonManageIsSuccessful(): void
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
                ->setController(SalesPersonCrudController::class)
                ->setAction(Action::INDEX)
                ->generateUrl()
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $client->request(
            "GET",
            $adminUrlGenerator
                ->setController(SalesPersonCrudController::class)
                ->setAction(Action::NEW)
                ->generateUrl()
        );

        $this->assertResponseIsSuccessful();

        $client->submitForm("Créer", [
            "SalesPerson[firstName]" => "Prénom",
            "SalesPerson[lastName]" => "Nom",
            "SalesPerson[email]" => "new@email.com",
            "SalesPerson[plainPassword]" => "password",
            "SalesPerson[phone]" => "0123456789",
            "SalesPerson[member]" => 2
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->request(
            "GET",
            $adminUrlGenerator
                ->setController(SalesPersonCrudController::class)
                ->setAction(Action::EDIT)
                ->setEntityId(6)
                ->generateUrl()
        );

        $this->assertResponseIsSuccessful();

        $client->submitForm("Sauvegarder les modifications", [
            "SalesPerson[firstName]" => "Prénom",
            "SalesPerson[lastName]" => "Nom",
            "SalesPerson[email]" => "new+1@email.com",
            "SalesPerson[phone]" => "0123456789",
            "SalesPerson[member]" => 2
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        /** @var SalesPerson $salesPerson */
        $salesPerson = $entityManager->getRepository(SalesPerson::class)->findOneByEmail("new@email.com");

        $client->request(
            "GET",
            $adminUrlGenerator
                ->setController(SalesPersonCrudController::class)
                ->setAction(Action::DELETE)
                ->setEntityId($salesPerson->getId())
                ->generateUrl()
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->request(
            "GET",
            $adminUrlGenerator
                ->setController(SalesPersonCrudController::class)
                ->setAction(Action::DETAIL)
                ->setEntityId(6)
                ->generateUrl()
        );

        $this->assertResponseIsSuccessful();
    }
}
