<?php

declare(strict_types=1);

namespace App\Tests\Functional\Admin;

use App\Controller\Admin\CustomerCrudController;
use App\Entity\Administrator;
use App\Entity\User\Customer;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CustomerTest extends WebTestCase
{
    public function testIfCustomerManageIsSuccessful(): void
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
                ->setController(CustomerCrudController::class)
                ->setAction(Action::INDEX)
                ->generateUrl()
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $client->request(
            "GET",
            $adminUrlGenerator
                ->setController(CustomerCrudController::class)
                ->setAction(Action::NEW)
                ->generateUrl()
        );

        $this->assertResponseIsSuccessful();

        $client->submitForm("Créer", [
            "Customer[firstName]" => "Prénom",
            "Customer[lastName]" => "Nom",
            "Customer[email]" => "new@email.com",
            "Customer[plainPassword]" => "password",
            "Customer[client]" => 7
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->request(
            "GET",
            $adminUrlGenerator
                ->setController(CustomerCrudController::class)
                ->setAction(Action::EDIT)
                ->setEntityId(16)
                ->generateUrl()
        );

        $this->assertResponseIsSuccessful();

        $client->submitForm("Sauvegarder les modifications", [
            "Customer[firstName]" => "Prénom",
            "Customer[lastName]" => "Nom",
            "Customer[email]" => "new+1@email.com",
            "Customer[client]" => 7
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        /** @var Customer $customer */
        $customer = $entityManager->getRepository(Customer::class)->findOneByEmail("new@email.com");

        $client->request(
            "GET",
            $adminUrlGenerator
                ->setController(CustomerCrudController::class)
                ->setAction(Action::DELETE)
                ->setEntityId($customer->getId())
                ->generateUrl()
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->request(
            "GET",
            $adminUrlGenerator
                ->setController(CustomerCrudController::class)
                ->setAction(Action::DETAIL)
                ->setEntityId(16)
                ->generateUrl()
        );

        $this->assertResponseIsSuccessful();
    }
}
