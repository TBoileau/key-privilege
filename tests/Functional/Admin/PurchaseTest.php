<?php

namespace App\Tests\Functional\Admin;

use App\Entity\Administrator;
use App\Entity\Key\Purchase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PurchaseTest extends WebTestCase
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

        $client->clickLink("Achats de points");

        $this->assertResponseIsSuccessful();

        $crawler = $client->clickLink("Créer Achat de points");

        $this->assertResponseIsSuccessful();

        $client->submit($crawler->selectButton("Créer")->form([
            "ea[newForm][btn]" => "saveAndReturn",
            "Purchase[account]" => 1,
            "Purchase[mode]" => Purchase::MODE_CHECK,
            "Purchase[internReference]" => "REF",
            "Purchase[points]" => 500
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();

        $client->clickLink("Accepter");

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();

        $client->clickLink("Retour à la liste");

        $this->assertResponseIsSuccessful();

        $crawler = $client->clickLink("Créer Achat de points");

        $this->assertResponseIsSuccessful();

        $client->submit($crawler->selectButton("Créer")->form([
            "ea[newForm][btn]" => "saveAndReturn",
            "Purchase[account]" => 1,
            "Purchase[mode]" => Purchase::MODE_CHECK,
            "Purchase[internReference]" => "REF",
            "Purchase[points]" => 500
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();

        $client->clickLink("Refuser");

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();

        $client->clickLink("Retour à la liste");

        $this->assertResponseIsSuccessful();

        $crawler = $client->clickLink("Créer Achat de points");

        $this->assertResponseIsSuccessful();

        $client->submit($crawler->selectButton("Créer")->form([
            "ea[newForm][btn]" => "saveAndReturn",
            "Purchase[account]" => 1,
            "Purchase[mode]" => Purchase::MODE_CHECK,
            "Purchase[internReference]" => "REF",
            "Purchase[points]" => 500
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();

        $client->clickLink("Annuler");

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();

        $client->clickLink("Retour à la liste");

        $this->assertResponseIsSuccessful();
    }
}
