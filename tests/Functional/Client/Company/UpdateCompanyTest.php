<?php

declare(strict_types=1);

namespace App\Tests\Functional\Client\Company;

use App\Entity\Company\Client;
use App\Entity\Company\Member;
use App\Entity\User\Manager;
use App\Entity\User\SalesPerson;
use Doctrine\ORM\EntityManagerInterface;
use Generator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UpdateCompanyTest extends WebTestCase
{
    public function testAsManagerIfUpdateCompanyIsForbidden(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Manager $manager */
        $manager = $entityManager->find(Manager::class, 1);

        $client->loginUser($manager);

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        $client->request(Request::METHOD_GET, $urlGenerator->generate("client_company_update", ["id" => 27]));

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAsSalesPersonIfUpdateCompanyIsForbidden(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var SalesPerson $salesPerson */
        $salesPerson = $entityManager->find(SalesPerson::class, 7);

        $client->loginUser($salesPerson);

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        $client->request(Request::METHOD_GET, $urlGenerator->generate("client_company_update", ["id" => 16]));

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAsManagerIfUpdateCompanyIsSuccessful(): void
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

        $client->request(Request::METHOD_GET, $urlGenerator->generate("client_company_update", ["id" => 27]));

        $this->assertResponseIsSuccessful();

        $client->submitForm("Modifier", [
            "company[name]" => "Raison sociale",
            "company[companyNumber]" => "44306184100047",
            "company[member]" => 3,
            "company[address][streetAddress]" => "1 rue de la mairie",
            "company[address][restAddress]" => "Batiment A",
            "company[address][zipCode]" => "75000",
            "company[address][locality]" => "Paris",
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Client $clientCompany */
        $clientCompany = $entityManager->getRepository(Client::class)->findOneByName("Raison sociale");

        $this->assertEquals("RAISON SOCIALE", $clientCompany->getName());
        $this->assertEquals("FR64443061841", $clientCompany->getVatNumber());
        $this->assertEquals("44306184100047", $clientCompany->getCompanyNumber());
        $this->assertEquals(3, $clientCompany->getMember()->getId());
        $this->assertEquals(7, $clientCompany->getSalesPerson()->getId());

        $client->followRedirect();

        $this->assertRouteSame("client_company_list");
    }

    /**
     * @dataProvider provideFailedData
     */
    public function testIfUpdateCompanyFailed(array $formData, string $errorMessage): void
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

        $client->request(Request::METHOD_GET, $urlGenerator->generate("client_company_update", ["id" => 27]));

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
                "company[name]" => "",
                "company[companyNumber]" => "44306184100047",
                "company[member]" => 3,
                "company[salesPerson]" => 7,
                "company[address][streetAddress]" => "1 rue de la mairie",
                "company[address][restAddress]" => "Batiment A",
                "company[address][zipCode]" => "75000",
                "company[address][locality]" => "Paris",
            ],
            "Cette valeur ne doit pas être vide."
        ];

        yield [
            [
                "company[name]" => "Raison sociale",
                "company[companyNumber]" => "44306184100047",
                "company[member]" => 3,
                "company[salesPerson]" => 7,
                "company[address][streetAddress]" => "1 rue de la mairie",
                "company[address][restAddress]" => "Batiment A",
                "company[address][zipCode]" => "fail",
                "company[address][locality]" => "Paris"
            ],
            "Code postal invalide."
        ];

        yield [
            [
                "company[name]" => "Raison sociale",
                "company[companyNumber]" => "44306184100047",
                "company[member]" => 3,
                "company[salesPerson]" => 7,
                "company[address][streetAddress]" => "",
                "company[address][restAddress]" => "Batiment A",
                "company[address][zipCode]" => "75000",
                "company[address][locality]" => "Paris"
            ],
            "Cette valeur ne doit pas être vide."
        ];

        yield [
            [
                "company[name]" => "Raison sociale",
                "company[companyNumber]" => "44306184100047",
                "company[member]" => 3,
                "company[salesPerson]" => 7,
                "company[address][streetAddress]" => "1 rue de la mairie",
                "company[address][restAddress]" => "Batiment A",
                "company[address][zipCode]" => "",
                "company[address][locality]" => "Paris"
            ],
            "Cette valeur ne doit pas être vide."
        ];

        yield [
            [
                "company[name]" => "Raison sociale",
                "company[companyNumber]" => "44306184100047",
                "company[member]" => 3,
                "company[salesPerson]" => 7,
                "company[address][streetAddress]" => "1 rue de la mairie",
                "company[address][restAddress]" => "Batiment A",
                "company[address][zipCode]" => "75000",
                "company[address][locality]" => ""
            ],
            "Cette valeur ne doit pas être vide."
        ];

        yield [
            [
                "company[name]" => "Raison sociale",
                "company[companyNumber]" => "fail",
                "company[member]" => 3,
                "company[salesPerson]" => 7,
                "company[address][streetAddress]" => "1 rue de la mairie",
                "company[address][restAddress]" => "Batiment A",
                "company[address][zipCode]" => "75000",
                "company[address][locality]" => "Paris",
            ],
            'Le N° de SIRET "fail" n\'est pas valide.'
        ];

        yield [
            [
                "company[name]" => "Raison sociale",
                "company[companyNumber]" => "12345678901234",
                "company[member]" => 3,
                "company[salesPerson]" => 7,
                "company[address][streetAddress]" => "1 rue de la mairie",
                "company[address][restAddress]" => "Batiment A",
                "company[address][zipCode]" => "75000",
                "company[address][locality]" => "Paris",
            ],
            'Le N° de SIRET "12345678901234" n\'est pas valide.'
        ];

        yield [
            [
                "company[name]" => "Raison sociale",
                "company[companyNumber]" => "12345678901234",
                "company[member]" => 3,
                "company[salesPerson]" => 6,
                "company[address][streetAddress]" => "1 rue de la mairie",
                "company[address][restAddress]" => "Batiment A",
                "company[address][zipCode]" => "75000",
                "company[address][locality]" => "Paris",
            ],
            'Le/la commercial(e) rattaché(e) n\'appartient à l\'adhérent sélectionné.'
        ];
    }
}
