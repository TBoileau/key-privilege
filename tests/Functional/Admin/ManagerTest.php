<?php

declare(strict_types=1);

namespace App\Tests\Functional\Admin;

use App\Controller\Admin\ManagerCrudController;
use App\Entity\Administrator;
use App\Entity\User\Manager;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ManagerTest extends WebTestCase
{
    public function testIfManagerManageIsSuccessful(): void
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
                ->setController(ManagerCrudController::class)
                ->setAction(Action::INDEX)
                ->generateUrl()
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $client->request(
            "GET",
            $adminUrlGenerator
                ->setController(ManagerCrudController::class)
                ->setAction(Action::NEW)
                ->generateUrl()
        );

        $this->assertResponseIsSuccessful();

        $client->submitForm("Créer", [
            "Manager[firstName]" => "Prénom",
            "Manager[lastName]" => "Nom",
            "Manager[email]" => "new@email.com",
            "Manager[plainPassword]" => "password",
            "Manager[phone]" => "0123456789",
            "Manager[member]" => 2,
            "Manager[members]" => [2, 3, 4, 5]
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->request(
            "GET",
            $adminUrlGenerator
                ->setController(ManagerCrudController::class)
                ->setAction(Action::EDIT)
                ->setEntityId(1)
                ->generateUrl()
        );

        $this->assertResponseIsSuccessful();

        $client->submitForm("Sauvegarder les modifications", [
            "Manager[firstName]" => "Prénom",
            "Manager[lastName]" => "Nom",
            "Manager[email]" => "new+1@email.com",
            "Manager[phone]" => "0123456789",
            "Manager[member]" => 3,
            "Manager[members]" => [2, 4, 5]
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        /** @var Manager $manager */
        $manager = $entityManager->getRepository(Manager::class)->findOneByEmail("new@email.com");

        $client->request(
            "GET",
            $adminUrlGenerator
                ->setController(ManagerCrudController::class)
                ->setAction(Action::DELETE)
                ->setEntityId($manager->getId())
                ->generateUrl()
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->request(
            "GET",
            $adminUrlGenerator
                ->setController(ManagerCrudController::class)
                ->setAction(Action::DETAIL)
                ->setEntityId(1)
                ->generateUrl()
        );

        $this->assertResponseIsSuccessful();
    }
}
