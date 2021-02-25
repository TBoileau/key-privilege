<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AccessListTest extends WebTestCase
{
    public function testIfAccessListIsSuccessful(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var User $user */
        $user = $entityManager->find(User::class, 1);

        $client->loginUser($user);

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        $crawler = $client->request(Request::METHOD_GET, $urlGenerator->generate("access_list"));
        $this->assertPage($crawler, 10, true, 1, false, true);

        $crawler = $client->clickLink("Suivant");
        $this->assertPage($crawler, 10, true, 2, true, true);

        $crawler = $client->clickLink("Précédent");
        $this->assertPage($crawler, 10, true, 1, false, true);

        $crawler = $client->clickLink("2");
        $this->assertPage($crawler, 10, true, 2, true, true);

        $crawler = $client->submitForm("Filtrer", [
            "access_filter[keywords]" => "Prénom"
        ]);

        $this->assertPage($crawler, 1, false, 1, false, false);
    }

    private function assertPage(
        Crawler $crawler,
        int $rows,
        bool $pagination,
        int $currentPage,
        bool $previous,
        bool $next
    ): void {
        $this->assertResponseIsSuccessful();
        $this->assertEquals($pagination, $crawler->filter("ul.pagination")->count() > 0);

        if ($pagination) {
            $this->assertEquals(
                $currentPage,
                (int) $crawler->filter("ul.pagination li.active > a.page-link")->text()
            );
            $this->assertEquals(
                $previous,
                $crawler->filter("ul.pagination a.page-link[data-role=previous]")->count() > 0
            );
            $this->assertEquals(
                $next,
                $crawler->filter("ul.pagination a.page-link[data-role=next]")->count() > 0
            );
        }

        $this->assertCount($rows, $crawler->filter("table[data-role=accessList] > tbody > tr"));
    }
}
