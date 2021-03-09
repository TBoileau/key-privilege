<?php

declare(strict_types=1);

namespace App\Tests\Functional\Member\Access;

use App\Entity\User\Manager;
use App\Entity\User\Customer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DeleteAccessTest extends WebTestCase
{
    public function testIfDeletingAccessListIsSuccessful(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Manager $manager */
        $manager = $entityManager->find(Manager::class, 1);

        /** @var Customer $user */
        $user = $entityManager->find(Customer::class, 16);

        $client->loginUser($manager);

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        $client->request(
            Request::METHOD_GET,
            $urlGenerator->generate("member_access_delete", ["id" => $user->getId()])
        );

        $client->submitForm("Supprimer", []);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->getContainer()
            ->get("doctrine.orm.entity_manager")
            ->getFilters()
            ->disable("softdeleteable");

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Customer $user */
        $user = $entityManager->find(Customer::class, $user->getId());

        $this->assertTrue($user->isDeleted());

        $client->followRedirect();

        $this->assertRouteSame("member_access_list");
    }
}
