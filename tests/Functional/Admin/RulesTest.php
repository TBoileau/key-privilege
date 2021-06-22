<?php

declare(strict_types=1);

namespace App\Tests\Functional\Admin;

use App\Controller\Admin\ManagerCrudController;
use App\Controller\Admin\RulesCrudController;
use App\Entity\Administrator;
use App\Entity\Rules;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class RulesTest extends WebTestCase
{
    public function testIfRulesManageIsSuccessful(): void
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
                ->setController(RulesCrudController::class)
                ->setAction(Action::INDEX)
                ->generateUrl()
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $client->request(
            "GET",
            $adminUrlGenerator
                ->setController(RulesCrudController::class)
                ->setAction(Action::NEW)
                ->generateUrl()
        );

        $this->assertResponseIsSuccessful();

        $client->submitForm("Créer", [
            "Rules[publishedAt]" => (new \DateTimeImmutable("1 month"))->format("Y-m-d H:i"),
            "Rules[content]" => "Content"
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        /** @var Rules $rules */
        $rules = $entityManager->getRepository(Rules::class)->findOneByContent("Content");

        $client->request(
            "GET",
            $adminUrlGenerator
                ->setController(RulesCrudController::class)
                ->setAction(Action::EDIT)
                ->setEntityId($rules->getId())
                ->generateUrl()
        );

        $this->assertResponseIsSuccessful();

        $client->submitForm("Sauvegarder les modifications", [
            "Rules[publishedAt]" => (new \DateTimeImmutable("2 month"))->format("Y-m-d H:i"),
            "Rules[content]" => "Content"
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->request(
            "GET",
            $adminUrlGenerator
                ->setController(RulesCrudController::class)
                ->setAction(Action::DELETE)
                ->setEntityId($rules->getId())
                ->generateUrl()
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->request(
            "GET",
            $adminUrlGenerator
                ->setController(RulesCrudController::class)
                ->setAction(Action::DELETE)
                ->setEntityId(1)
                ->generateUrl()
        );
    }
}
