<?php

declare(strict_types=1);

namespace App\Tests\Functional\Member\Access;

use App\Entity\User\Collaborator;
use App\Entity\User\Manager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SuspendAccessTest extends WebTestCase
{
    public function testIfSuspendAccessIsSuccessful(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Manager $manager */
        $manager = $entityManager->find(Manager::class, 1);

        /** @var Collaborator $collaborator */
        $collaborator = $entityManager->find(Collaborator::class, 11);

        $client->loginUser($manager);

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        $client->request(
            Request::METHOD_GET,
            $urlGenerator->generate("member_access_suspend", ["id" => $collaborator->getId()])
        );

        $client->submitForm("Suspendre", []);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Collaborator $collaborator */
        $collaborator = $entityManager->find(Collaborator::class, $collaborator->getId());

        $this->assertTrue($collaborator->isSuspended());

        $client->followRedirect();

        $this->assertRouteSame("member_access_list");

        $client->request(
            Request::METHOD_GET,
            $urlGenerator->generate("member_access_active", ["id" => $collaborator->getId()])
        );

        $client->submitForm("Activer", []);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Collaborator $collaborator */
        $collaborator = $entityManager->find(Collaborator::class, $collaborator->getId());

        $this->assertFalse($collaborator->isSuspended());

        $client->followRedirect();

        $this->assertRouteSame("member_access_list");
    }
}
