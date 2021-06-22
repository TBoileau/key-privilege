<?php

namespace App\Tests\Functional\Admin;

use App\Entity\Administrator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TransferTest extends WebTestCase
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

        $client->clickLink("Transferts");

        $this->assertResponseIsSuccessful();

        $crawler = $client->clickLink("Créer Transfert");

        $this->assertResponseIsSuccessful();

        $client->submit($crawler->selectButton("Créer")->form([
            "ea[newForm][btn]" => "saveAndReturn",
            "Transfer[from]" => 1,
            "Transfer[to]" => 2,
            "Transfer[points]" => 500
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $crawler = $client->followRedirect();

        $client->click($crawler->filter("table.datagrid > tbody > tr:first-child .action-detail")->link());

        $this->assertResponseIsSuccessful();

        $client->clickLink("Retour à la liste");

        $this->assertResponseIsSuccessful();
    }
}
