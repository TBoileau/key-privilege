<?php

declare(strict_types=1);

namespace App\Tests\Functional\Admin;

use App\Controller\Admin\CollaboratorCrudController;
use App\Entity\Administrator;
use App\Entity\User\Collaborator;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CollaboratorTest extends WebTestCase
{
    public function testIfCollaboratorManageIsSuccessful(): void
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
                ->setController(CollaboratorCrudController::class)
                ->setAction(Action::INDEX)
                ->generateUrl()
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $client->request(
            "GET",
            $adminUrlGenerator
                ->setController(CollaboratorCrudController::class)
                ->setAction(Action::NEW)
                ->generateUrl()
        );

        $this->assertResponseIsSuccessful();

        $client->submitForm("Créer", [
            "Collaborator[firstName]" => "Prénom",
            "Collaborator[lastName]" => "Nom",
            "Collaborator[email]" => "new@email.com",
            "Collaborator[plainPassword]" => "password",
            "Collaborator[phone]" => "0123456789",
            "Collaborator[member]" => 2
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->request(
            "GET",
            $adminUrlGenerator
                ->setController(CollaboratorCrudController::class)
                ->setAction(Action::EDIT)
                ->setEntityId(6)
                ->generateUrl()
        );

        $this->assertResponseIsSuccessful();

        $client->submitForm("Sauvegarder les modifications", [
            "Collaborator[firstName]" => "Prénom",
            "Collaborator[lastName]" => "Nom",
            "Collaborator[email]" => "new+1@email.com",
            "Collaborator[phone]" => "0123456789",
            "Collaborator[member]" => 2
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        /** @var Collaborator $collaborator */
        $collaborator = $entityManager->getRepository(Collaborator::class)->findOneByEmail("new@email.com");

        $client->request(
            "GET",
            $adminUrlGenerator
                ->setController(CollaboratorCrudController::class)
                ->setAction(Action::DELETE)
                ->setEntityId($collaborator->getId())
                ->generateUrl()
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->request(
            "GET",
            $adminUrlGenerator
                ->setController(CollaboratorCrudController::class)
                ->setAction(Action::DETAIL)
                ->setEntityId(6)
                ->generateUrl()
        );

        $this->assertResponseIsSuccessful();
    }
}
