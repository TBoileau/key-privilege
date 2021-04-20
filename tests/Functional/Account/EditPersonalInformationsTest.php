<?php

declare(strict_types=1);

namespace App\Tests\Functional\Account;

use App\Entity\User\Employee;
use App\Entity\User\Manager;
use App\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;
use Generator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EditPersonalInformationsTest extends WebTestCase
{
    public function testIfEditPersonalInformationsIsSuccessful(): void
    {
        $client = static::createClient();

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var User $user */
        $user = $entityManager->find(User::class, 1);

        $client->loginUser($user);

        $crawler = $client->request(
            Request::METHOD_GET,
            $urlGenerator->generate("account_edit_personal_informations")
        );

        $this->assertResponseIsSuccessful();

        $client->submit($crawler->filter("form[name=edit_personal_informations]")->form([
            "edit_personal_informations[firstName]" => "Bernard",
            "edit_personal_informations[lastName]" => "Duchemin",
            "edit_personal_informations[email]" => "edit@email.com",
            "edit_personal_informations[phone]" => "0123456789"
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Manager $user */
        $user = $entityManager->find(User::class, 1);

        $this->assertNotNull($user);
        $this->assertEquals("BERNARD", $user->getFirstName());
        $this->assertEquals("DUCHEMIN", $user->getLastName());
        $this->assertEquals("edit@email.com", $user->getEmail());
        $this->assertEquals("BERNARD DUCHEMIN", $user->getFullName());
        $this->assertEquals("0123456789", $user->getPhone());

        $client->followRedirect();

        $this->assertRouteSame("account_index");
    }

    /**
     * @dataProvider provideBadDataForEditPersonalInformations
     */
    public function testIfEditPersonalInformationsFormIsInvalid(array $formData, string $errorMessage): void
    {
        $client = static::createClient();

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var User $user */
        $user = $entityManager->find(User::class, 1);

        $client->loginUser($user);

        $crawler = $client->request(
            Request::METHOD_GET,
            $urlGenerator->generate("account_edit_personal_informations")
        );

        $this->assertResponseIsSuccessful();

        $client->submit($crawler->filter("form[name=edit_personal_informations]")->form($formData));

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertSelectorTextContains(
            ".form-error-message",
            $errorMessage
        );
    }

    public function provideBadDataForEditPersonalInformations(): Generator
    {
        yield [
            [
                "edit_personal_informations[firstName]" => "Jean",
                "edit_personal_informations[lastName]" => "Dupont",
                "edit_personal_informations[email]" => "fail",
                "edit_personal_informations[phone]" => "0123456789"
            ],
            "Cette valeur n'est pas une adresse email valide."
        ];

        yield [
            [
                "edit_personal_informations[firstName]" => "Jean",
                "edit_personal_informations[lastName]" => "Dupont",
                "edit_personal_informations[email]" => "",
                "edit_personal_informations[phone]" => "0123456789"
            ],
            "Cette valeur ne doit pas être vide."
        ];

        yield [
            [
                "edit_personal_informations[firstName]" => "",
                "edit_personal_informations[lastName]" => "Dupont",
                "edit_personal_informations[email]" => "edit@email.com",
                "edit_personal_informations[phone]" => "0123456789"
            ],
            "Cette valeur ne doit pas être vide."
        ];

        yield [
            [
                "edit_personal_informations[firstName]" => "Jean",
                "edit_personal_informations[lastName]" => "",
                "edit_personal_informations[email]" => "edit@email.com",
                "edit_personal_informations[phone]" => "0123456789"
            ],
            "Cette valeur ne doit pas être vide."
        ];

        yield [
            [
                "edit_personal_informations[firstName]" => "Prénom",
                "edit_personal_informations[lastName]" => "Nom",
                "edit_personal_informations[email]" => "new@email.com",
                "edit_personal_informations[phone]" => ""
            ],
            "Cette valeur ne doit pas être vide."
        ];

        yield [
            [
                "edit_personal_informations[firstName]" => "Prénom",
                "edit_personal_informations[lastName]" => "Nom",
                "edit_personal_informations[email]" => "new@email.com",
                "edit_personal_informations[phone]" => "fail"
            ],
            "Cette valeur n'est pas un numéro de téléphone valide."
        ];
    }
}
