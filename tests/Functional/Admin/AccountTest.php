<?php

namespace App\Tests\Functional\Admin;

use App\Entity\Administrator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class AccountTest extends WebTestCase
{
    /**
     * @test
     */
    public function crudWorks(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        $user = $entityManager->getRepository(Administrator::class)->findOneBy([]);

        $client->loginUser($user, 'admin');

        $client->request(Request::METHOD_GET, '/admin');

        $this->assertResponseIsSuccessful();

        $crawler = $client->clickLink("Comptes points");

        $this->assertResponseIsSuccessful();

        $client->click($crawler->filter("table.datagrid > tbody > tr:first-child .action-detail")->link());

        $this->assertResponseIsSuccessful();
    }
}
