<?php

declare(strict_types=1);

namespace App\Tests\Functional\Client\Access;

use App\Entity\Company\Member;
use App\Entity\User\Customer;
use App\Entity\User\Manager;
use App\Entity\User\SalesPerson;
use Doctrine\ORM\EntityManagerInterface;
use Generator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UpdateAccessTest extends WebTestCase
{
    public function testAsManagerIfUpdateAccessIsDenied(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Manager $manager */
        $manager = $entityManager->find(Manager::class, 1);

        $client->loginUser($manager);

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        $client->request(Request::METHOD_GET, $urlGenerator->generate("client_access_update", ["id" => 36]));

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAsSalesPersonIfUpdateAccessIsDenied(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var SalesPerson $salesPerson */
        $salesPerson = $entityManager->find(SalesPerson::class, 7);

        $client->loginUser($salesPerson);

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        $client->request(Request::METHOD_GET, $urlGenerator->generate("client_access_update", ["id" => 16]));

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAsSalesPersonIfAddAccessIsSuccessful(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var SalesPerson $salesPerson */
        $salesPerson = $entityManager->find(SalesPerson::class, 7);

        $client->loginUser($salesPerson);

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        $client->request(Request::METHOD_GET, $urlGenerator->generate("client_access_update", ["id" => 36]));

        $this->assertResponseIsSuccessful();

        $client->submitForm("Modifier", [
            "access[firstName]" => "Prénom",
            "access[lastName]" => "Nom",
            "access[email]" => "new@email.com",
            "access[client]" => 27
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Customer $customer */
        $customer = $entityManager->getRepository(Customer::class)->findOneByEmail("new@email.com");

        $this->assertEquals("PRÉNOM", $customer->getFirstName());
        $this->assertEquals("NOM", $customer->getLastName());
        $this->assertEquals("new@email.com", $customer->getEmail());
        $this->assertEquals(27, $customer->getClient()->getId());

        $client->followRedirect();

        $this->assertRouteSame("client_access_list");
    }

    public function testAsManagerIfAddAccessIsSuccessful(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Manager $manager */
        $manager = $entityManager->find(Manager::class, 1);

        /** @var Member $menber */
        $member = $entityManager->find(Member::class, 3);

        $manager->getMembers()->add($member);

        $entityManager->flush();

        $client->loginUser($manager);

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        $client->request(Request::METHOD_GET, $urlGenerator->generate("client_access_update", ["id" => 16]));

        $this->assertResponseIsSuccessful();

        $client->submitForm("Modifier", [
            "access[firstName]" => "Prénom",
            "access[lastName]" => "Nom",
            "access[email]" => "new@email.com",
            "access[client]" => 16
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Customer $customer */
        $customer = $entityManager->getRepository(Customer::class)->findOneByEmail("new@email.com");

        $this->assertEquals("PRÉNOM", $customer->getFirstName());
        $this->assertEquals("NOM", $customer->getLastName());
        $this->assertEquals("new@email.com", $customer->getEmail());
        $this->assertEquals(16, $customer->getClient()->getId());

        $client->followRedirect();

        $this->assertRouteSame("client_access_list");
    }

    /**
     * @dataProvider provideFailedData
     */
    public function testIfAddAccessFailed(array $formData, string $errorMessage): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Manager $manager */
        $manager = $entityManager->find(Manager::class, 1);

        $client->loginUser($manager);

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        $client->request(Request::METHOD_GET, $urlGenerator->generate("client_access_update", ["id" => 16]));

        $this->assertResponseIsSuccessful();

        $client->submitForm("Modifier", $formData);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertSelectorTextContains(
            ".form-error-message",
            $errorMessage
        );
    }

    public function provideFailedData(): Generator
    {
        yield [
            [
                "access[firstName]" => "",
                "access[lastName]" => "Nom",
                "access[email]" => "new@email.com",
                "access[client]" => 16
            ],
            "Cette valeur ne doit pas être vide."
        ];

        yield [
            [
                "access[firstName]" => "Prénom",
                "access[lastName]" => "",
                "access[email]" => "new@email.com",
                "access[client]" => 16
            ],
            "Cette valeur ne doit pas être vide."
        ];

        yield [
            [
                "access[firstName]" => "Prénom",
                "access[lastName]" => "Nom",
                "access[email]" => "",
                "access[client]" => 16
            ],
            "Cette valeur ne doit pas être vide."
        ];

        yield [
            [
                "access[firstName]" => "Prénom",
                "access[lastName]" => "Nom",
                "access[email]" => "fail",
                "access[client]" => 16
            ],
            "Cette valeur n'est pas une adresse email valide."
        ];
    }
}
