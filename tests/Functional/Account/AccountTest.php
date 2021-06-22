<?php

declare(strict_types=1);

namespace App\Tests\Functional\Account;

use App\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AccountTest extends WebTestCase
{
    public function testIfAccountPageIsSuccessful(): void
    {
        $client = static::createClient();

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var User $user */
        $user = $entityManager->find(User::class, 1);

        $client->loginUser($user);

        $crawler = $client->request(Request::METHOD_GET, $urlGenerator->generate("account_index"));

        $this->assertResponseIsSuccessful();

        $this->assertSelectorTextContains("[data-role=firstName]", $user->getFirstName());
        $this->assertSelectorTextContains("[data-role=lastName]", $user->getLastName());
        $this->assertSelectorTextContains("[data-role=email]", $user->getEmail());
        $this->assertEquals(1, $crawler->filter("div.list-group[data-role=rules] > a.list-group-item")->count());
    }
}
