<?php

declare(strict_types=1);

namespace App\Tests\Functional\Address;

use App\Entity\Address;
use App\Entity\User\Manager;
use App\Entity\User\Customer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DeleteTest extends WebTestCase
{
    public function testIfDeleteAddressIsSuccessful(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Manager $manager */
        $manager = $entityManager->find(Manager::class, 1);

        $address = (new Address())
            ->setFirstName("John")
            ->setLastName("Doe")
            ->setCompanyName("Société")
            ->setLocality("Paris")
            ->setZipCode("75000")
            ->setEmail("email@email.com")
            ->setPhone("0123456789")
            ->setStreetAddress("1 rue de la mairie");

        $manager->setDeliveryAddress($address);

        $manager->getDeliveryAddresses()->add($address);

        $entityManager->flush();

        $client->loginUser($manager);

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        $client->request(
            Request::METHOD_GET,
            $urlGenerator->generate("address_delete", ["id" => $manager->getDeliveryAddress()->getId()])
        );

        $client->submitForm("Supprimer", []);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();

        $this->assertRouteSame("address_list");
    }
}
