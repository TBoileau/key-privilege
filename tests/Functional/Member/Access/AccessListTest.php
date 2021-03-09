<?php

declare(strict_types=1);

namespace App\Tests\Functional\Member\Access;

use App\Entity\User\Collaborator;
use App\Entity\User\Customer;
use App\Entity\User\Manager;
use App\Entity\User\SalesPerson;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AccessListTest extends WebTestCase
{
    public function testAsCollaboratorIfAccessListIsForbidden(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Collaborator $collaborator */
        $collaborator = $entityManager->find(Collaborator::class, 11);

        $client->loginUser($collaborator);

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        $client->request(Request::METHOD_GET, $urlGenerator->generate("member_access_list"));

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAsSalesPersonIfAccessListIsForbidden(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Collaborator $collaborator */
        $collaborator = $entityManager->find(SalesPerson::class, 7);

        $client->loginUser($collaborator);

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        $client->request(Request::METHOD_GET, $urlGenerator->generate("member_access_list"));

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAsCustomerIfAccessListIsForbidden(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Customer $customer */
        $customer = $entityManager->find(Customer::class, 16);

        $client->loginUser($customer);

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        $client->request(Request::METHOD_GET, $urlGenerator->generate("member_access_list"));

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAsManagerIfAccessListIsWorks(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Manager $manager */
        $manager = $entityManager->find(Manager::class, 1);

        $client->loginUser($manager);

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        $crawler = $client->request(Request::METHOD_GET, $urlGenerator->generate("member_access_list"));

        $this->assertPage($crawler, 2, false, 1, false, false);

        $crawler = $client->submitForm("Filtrer", [
            "filter[keywords]" => "Fail"
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
