<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\User;
use App\Repository\UserRepository;
use Generator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class EditPersonalInformationsTest extends WebTestCase
{
    public function testIfEditPersonalInformationsIsSuccessful(): void
    {
        $client = static::createClient();

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        /** @var UserRepository $userRepository */
        $userRepository = $client->getContainer()
            ->get("doctrine.orm.entity_manager")
            ->getRepository(User::class);

        /** @var User $user */
        $user = $userRepository->findOneBy(["email" => "user@email.com"]);

        $client->loginUser($user);

        $crawler = $client->request(
            Request::METHOD_GET,
            $urlGenerator->generate("account_edit_personal_informations")
        );

        $this->assertResponseIsSuccessful();

        $client->submit($crawler->filter("form[name=edit_personal_informations]")->form([
            "edit_personal_informations[firstName]" => "Bernard",
            "edit_personal_informations[lastName]" => "Duchemin",
            "edit_personal_informations[email]" => "new+user@email.com"
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        /** @var User $user */
        $user = $userRepository->findOneBy(["email" => "new+user@email.com"]);

        $this->assertNotNull($user);
        $this->assertEquals("Bernard", $user->getFirstName());
        $this->assertEquals("Duchemin", $user->getLastName());
        $this->assertEquals("new+user@email.com", $user->getEmail());
        $this->assertEquals("Bernard Duchemin", $user->getFullName());

        $client->followRedirect();

        $this->assertRouteSame("account_edit_personal_informations");
    }

    /**
     * @dataProvider provideBadDataForEditPersonalInformations
     */
    public function testIfEditPersonalInformationsFormIsInvalid(array $formData, string $errorMessage): void
    {
        $client = static::createClient();

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        /** @var UserRepository $userRepository */
        $userRepository = $client->getContainer()
            ->get("doctrine.orm.entity_manager")
            ->getRepository(User::class);

        /** @var User $user */
        $user = $userRepository->findOneBy(["email" => "user@email.com"]);

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
                "edit_personal_informations[email]" => "user+refused+rules@email.com"
            ],
            "Cette valeur est déjà utilisée."
        ];

        yield [
            [
                "edit_personal_informations[firstName]" => "Jean",
                "edit_personal_informations[lastName]" => "Dupont",
                "edit_personal_informations[email]" => "fail"
            ],
            "Cette valeur n'est pas une adresse email valide."
        ];

        yield [
            [
                "edit_personal_informations[firstName]" => "Jean",
                "edit_personal_informations[lastName]" => "Dupont",
                "edit_personal_informations[email]" => ""
            ],
            "Cette valeur ne doit pas être vide."
        ];

        yield [
            [
                "edit_personal_informations[firstName]" => "",
                "edit_personal_informations[lastName]" => "Dupont",
                "edit_personal_informations[email]" => "new+user@email.com"
            ],
            "Cette valeur ne doit pas être vide."
        ];

        yield [
            [
                "edit_personal_informations[firstName]" => "Jean",
                "edit_personal_informations[lastName]" => "",
                "edit_personal_informations[email]" => "new+user@email.com"
            ],
            "Cette valeur ne doit pas être vide."
        ];
    }
}
