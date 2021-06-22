<?php

declare(strict_types=1);

namespace App\Tests\Functional\Admin;

use App\Controller\Admin\AdministratorCrudController;
use App\Controller\Admin\OrganizationCrudController;
use App\Entity\Administrator;
use App\Entity\Company\Organization;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class OrganizationTest extends WebTestCase
{
    public function testIfOrganizationManageIsSuccessful(): void
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
                ->setController(OrganizationCrudController::class)
                ->setAction(Action::INDEX)
                ->generateUrl()
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $client->request(
            "GET",
            $adminUrlGenerator
                ->setController(OrganizationCrudController::class)
                ->setAction(Action::NEW)
                ->generateUrl()
        );

        $this->assertResponseIsSuccessful();

        $client->submitForm("Créer", [
            "Organization[name]" => "Raison sociale",
            "Organization[companyNumber]" => "44306184100047"
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->request(
            "GET",
            $adminUrlGenerator
                ->setController(OrganizationCrudController::class)
                ->setAction(Action::EDIT)
                ->setEntityId(1)
                ->generateUrl()
        );

        $this->assertResponseIsSuccessful();

        $client->submitForm("Sauvegarder les modifications", [
            "Organization[name]" => "Raison sociale",
            "Organization[companyNumber]" => "42878504200105"
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        /** @var Organization $organization */
        $organization = $entityManager->getRepository(Organization::class)->findOneByName("Raison sociale");

        $client->request(
            "GET",
            $adminUrlGenerator
                ->setController(OrganizationCrudController::class)
                ->setAction(Action::DELETE)
                ->setEntityId($organization->getId())
                ->generateUrl()
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->request(
            "GET",
            $adminUrlGenerator
                ->setController(OrganizationCrudController::class)
                ->setAction(Action::DETAIL)
                ->setEntityId(1)
                ->generateUrl()
        );

        $this->assertResponseIsSuccessful();
    }
}
