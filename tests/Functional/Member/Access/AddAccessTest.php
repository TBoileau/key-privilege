<?php

declare(strict_types=1);

namespace App\Tests\Functional\Member\Access;

use App\Entity\Administrator;
use App\Entity\Company\Member;
use App\Entity\User\Collaborator;
use App\Entity\User\Customer;
use App\Entity\User\Manager;
use App\Entity\User\SalesPerson;
use Doctrine\ORM\EntityManagerInterface;
use Generator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AddAccessTest extends WebTestCase
{
    /**
     * @dataProvider provideRoles
     */
    public function testAsManagerIfAddingAccessIsSuccessful(string $role, string $class): void
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

        $client->request(Request::METHOD_GET, $urlGenerator->generate("member_access_create", [
            "role" => $role
        ]));

        $this->assertResponseIsSuccessful();

        $client->submitForm("Créer", [
            "access[firstName]" => "Prénom",
            "access[lastName]" => "Nom",
            "access[email]" => "new@email.com",
            "access[phone]" => "0123456789",
            "access[member]" => 2
        ] + ($role === "administrateur" ? ["access[isInEmailCopy]" => 1] : []));

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Collaborator|SalesPerson|Administrator $user */
        $user = $entityManager->getRepository($class)->findOneByEmail("new@email.com");

        $this->assertEquals("PRÉNOM", $user->getFirstName());
        $this->assertEquals("NOM", $user->getLastName());
        $this->assertEquals("new@email.com", $user->getEmail());
        $this->assertEquals(2, $user->getMember()->getId());
        $this->assertEmailCount(1);

        $client->followRedirect();

        $this->assertRouteSame("member_access_list");
    }

    public function provideRoles(): Generator
    {
        yield ["administrateur", Manager::class];
        yield ["commercial", SalesPerson::class];
        yield ["collaborateur", Collaborator::class];
    }

    /**
     * @dataProvider provideFailedData
     */
    public function testIfAddingAccessFailed(array $formData, string $errorMessage): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Manager $manager */
        $manager = $entityManager->find(Manager::class, 1);

        $client->loginUser($manager);

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        $client->request(Request::METHOD_GET, $urlGenerator->generate("member_access_create", [
            "role" => "collaborateur"
        ]));

        $this->assertResponseIsSuccessful();

        $client->submitForm("Créer", $formData);

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
                "access[phone]" => "0123456789"
            ],
            "Cette valeur ne doit pas être vide."
        ];

        yield [
            [
                "access[firstName]" => "Prénom",
                "access[lastName]" => "",
                "access[email]" => "new@email.com",
                "access[phone]" => "0123456789"
            ],
            "Cette valeur ne doit pas être vide."
        ];

        yield [
            [
                "access[firstName]" => "Prénom",
                "access[lastName]" => "Nom",
                "access[email]" => "",
                "access[phone]" => "0123456789"
            ],
            "Cette valeur ne doit pas être vide."
        ];

        yield [
            [
                "access[firstName]" => "Prénom",
                "access[lastName]" => "Nom",
                "access[email]" => "fail",
                "access[phone]" => "0123456789"
            ],
            "Cette valeur n'est pas une adresse email valide."
        ];

        yield [
            [
                "access[firstName]" => "Prénom",
                "access[lastName]" => "Nom",
                "access[email]" => "new@email.com",
                "access[phone]" => ""
            ],
            "Cette valeur ne doit pas être vide."
        ];

        yield [
            [
                "access[firstName]" => "Prénom",
                "access[lastName]" => "Nom",
                "access[email]" => "new@email.com",
                "access[phone]" => "fail"
            ],
            "Cette valeur n'est pas un numéro de téléphone valide."
        ];
    }
}
