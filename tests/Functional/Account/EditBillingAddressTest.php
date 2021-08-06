<?php

declare(strict_types=1);

namespace App\Tests\Functional\Account;

use App\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;
use Generator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class EditBillingAddressTest extends WebTestCase
{
    public function testIfEditBillingAddressIsSuccessful(): void
    {
        $client = static::createClient();

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var User $user */
        $user = $entityManager->find(User::class, 1);

        $client->loginUser($user);

        $crawler = $client->request(Request::METHOD_GET, $urlGenerator->generate("account_edit_billing_address"));

        $this->assertResponseIsSuccessful();

        $client->submit($crawler->filter("form[name=address]")->form([
            "address[firstName]" => "John",
            "address[lastName]" => "Doe",
            "address[companyName]" => "Société",
            "address[professional]" => 1,
            "address[streetAddress]" => "1 rue de la mairie",
            "address[restAddress]" => "Batiment A",
            "address[zipCode]" => "75000",
            "address[locality]" => "Paris"
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();

        $this->assertRouteSame("account_index");
    }
}
