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

class ListTest extends WebTestCase
{
    public function testIfListAddressIsSuccessful(): void
    {
        $client = static::createClient();

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Manager $user */
        $user = $entityManager->find(User::class, 1);

        $client->loginUser($user);

        $client->request(Request::METHOD_GET, $urlGenerator->generate("address_list"));

        $this->assertResponseIsSuccessful();
    }
}
