<?php

declare(strict_types=1);

namespace App\Tests\Functional\Admin;

use App\Controller\Admin\AdministratorCrudController;
use App\Controller\Admin\MemberCrudController;
use App\Entity\Administrator;
use App\Entity\Company\Member;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class MemberTest extends WebTestCase
{
    public function testIfMemberManageIsSuccessful(): void
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
                ->setController(MemberCrudController::class)
                ->setAction(Action::INDEX)
                ->generateUrl()
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $client->request(
            "GET",
            $adminUrlGenerator
                ->setController(MemberCrudController::class)
                ->setAction(Action::NEW)
                ->generateUrl()
        );

        $this->assertResponseIsSuccessful();

        $client->submitForm("Créer", [
            "Member[name]" => "Raison sociale",
            "Member[companyNumber]" => "44306184100047",
            "Member[organization]" => 1,
            "Member[billingAddress_firstName]" => "John",
            "Member[billingAddress_lastName]" => "Doe",
            "Member[billingAddress_companyName]" => "Société",
            "Member[billingAddress_professional]" => "Oui",
            "Member[billingAddress_streetAddress]" => "1 rue de la mairie",
            "Member[billingAddress_zipCode]" => "75000",
            "Member[billingAddress_locality]" => "Paris",
            "Member[billingAddress_phone]" => "0123456789",
            "Member[billingAddress_email]" => "email@email.com"
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->request(
            "GET",
            $adminUrlGenerator
                ->setController(MemberCrudController::class)
                ->setAction(Action::EDIT)
                ->setEntityId(2)
                ->generateUrl()
        );

        $this->assertResponseIsSuccessful();

        $client->submitForm("Sauvegarder les modifications", [
            "Member[name]" => "Raison sociale",
            "Member[companyNumber]" => "42878504200105",
            "Member[organization]" => 1,
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        /** @var Member $member */
        $member = $entityManager->getRepository(Member::class)->findOneByName("Raison sociale");

        $client->request(
            "GET",
            $adminUrlGenerator
                ->setController(MemberCrudController::class)
                ->setAction(Action::DELETE)
                ->setEntityId($member->getId())
                ->generateUrl()
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->request(
            "GET",
            $adminUrlGenerator
                ->setController(MemberCrudController::class)
                ->setAction(Action::DETAIL)
                ->setEntityId(2)
                ->generateUrl()
        );

        $this->assertResponseIsSuccessful();
    }
}
