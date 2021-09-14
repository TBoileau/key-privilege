<?php

declare(strict_types=1);

namespace App\Tests\Functional\Address;

use App\Entity\User\Manager;
use App\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CreateTest extends WebTestCase
{
    public function testIfCreateDeliveryAddressIsSuccessful(): void
    {
        $client = static::createClient();

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Manager $user */
        $user = $entityManager->find(User::class, 1);

        $originalAddress = $user->getDeliveryAddress();

        $client->loginUser($user);

        $crawler = $client->request(Request::METHOD_GET, $urlGenerator->generate("address_create"));

        $this->assertResponseIsSuccessful();

        $client->submit($crawler->filter("form[name=new_address]")->form([
            "new_address[default]" => 1,
            "new_address[type]" => "delivery",
            "new_address[firstName]" => "John",
            "new_address[lastName]" => "Doe",
            "new_address[companyName]" => "Test",
            "new_address[professional]" => 1,
            "new_address[streetAddress]" => "1 rue de la mairie",
            "new_address[restAddress]" => "Batiment A",
            "new_address[zipCode]" => "75000",
            "new_address[locality]" => "Paris"
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();

        $this->assertRouteSame("address_list");

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Manager $user */
        $user = $entityManager->find(User::class, 1);

        $this->assertNotEquals($originalAddress, $user->getDeliveryAddress());
    }

    public function testIfCreateBillingAddressIsSuccessful(): void
    {
        $client = static::createClient();

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Manager $user */
        $user = $entityManager->find(User::class, 1);

        $originalAddress = $user->getMember()->getBillingAddress();

        $client->loginUser($user);

        $crawler = $client->request(Request::METHOD_GET, $urlGenerator->generate("address_create"));

        $this->assertResponseIsSuccessful();

        $client->submit($crawler->filter("form[name=new_address]")->form([
            "new_address[default]" => 1,
            "new_address[type]" => "billing",
            "new_address[firstName]" => "John",
            "new_address[lastName]" => "Doe",
            "new_address[companyName]" => "Test",
            "new_address[professional]" => 1,
            "new_address[streetAddress]" => "1 rue de la mairie",
            "new_address[restAddress]" => "Batiment A",
            "new_address[zipCode]" => "75000",
            "new_address[locality]" => "Paris"
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();

        $this->assertRouteSame("address_list");

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Manager $user */
        $user = $entityManager->find(User::class, 1);

        $this->assertNotEquals($originalAddress, $user->getMember()->getBillingAddress());
    }
}
